<?php

namespace App\Webhooks;

use App\Jobs\SendDispatchedWebhook;
use App\Models\Account;
use App\Models\DispatchedWebhook;
use App\Models\Webhook as WebhookModel;
use App\Models\WebhookEvent as WebhookEventModel;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Throwable;

class WebhookManager
{
    /**
     * List of available webhook events.
     */
    protected array $events = [];

    /**
     * Get list of available webhook definitions.
     *
     * @return \Illuminate\Support\Collection<int, \App\Webhooks\WebhookDefinition>
     */
    public function getAvailableEventDefinitions(): Collection
    {
        return collect($this->events)->map(fn (string $event) => $event::define());
    }

    /**
     * Register a Webhook event.
     */
    public function registerEvent(string $event): static
    {
        $this->events[] = $event;

        return $this;
    }

    /**
     * Dispatch a webhook event.
     */
    public function dispatch(Account $account, WebhookEvent $event): void
    {
        $webhookModel = new WebhookModel;
        $webhookEventModel = new WebhookEventModel;

        $webhooks = $account
            ->webhooks()
            ->select($webhookModel->qualifyColumn('*'))
            ->join($webhookEventModel->getTable(), $webhookModel->qualifyColumn('id'), $webhookEventModel->qualifyColumn('webhook_id'))
            ->where($webhookEventModel->qualifyColumn('event'), 'invoice.updated')
            ->where('active', true)
            ->get();

        if ($webhooks->isEmpty()) {
            return;
        }

        $eventName = $event::define()->id;
        $payload = $event->payload();

        $toDispatch = DB::transaction(fn () => $webhooks->map(function (WebhookModel $webhook) use ($eventName, $payload) {
            $pendingDispatch = new DispatchedWebhook([
                'event' => $eventName,
                'payload' => $payload,
                'attempts' => 0,
            ]);

            $pendingDispatch->webhook()->associate($webhook);

            $pendingDispatch->save();

            return $pendingDispatch;
        }));

        $toDispatch->each(fn (DispatchedWebhook $dispatch) => Bus::dispatch(new SendDispatchedWebhook($dispatch)));
    }

    /**
     * Attempt to send a webhook while locking.
     *
     * @throws \Illuminate\Contracts\Cache\LockTimeoutException
     */
    public function sendWhileLocked(DispatchedWebhook $dispatch): void
    {
        Cache::lock("WebhookDispatch:{$dispatch->uuid}", 15)->block(20, function () use ($dispatch) {
            $dispatch->refresh();

            $this->send($dispatch);
        });
    }

    /**
     * Attempt to send a dispatched webhook.
     */
    public function send(DispatchedWebhook $dispatch): void
    {
        if ($dispatch->delivered()) {
            return;
        }

        $event = [
            'id' => $dispatch->uuid,
            'event' => $dispatch->event,
            'payload' => $dispatch->payload,
            'dispatched_at' => $dispatch->created_at,
        ];

        $serialized = json_encode($event);

        $signature = hash_hmac('sha256', $serialized, $dispatch->webhook->secret);

        $dispatch->incrementAttempts();

        try {
            $response = Http::withHeaders(['X-Signature' => $signature])
                ->withoutRedirecting()
                ->withUserAgent('easyDoklad')
                ->withBody($serialized)
                ->when(app()->isLocal(), fn (PendingRequest $request) => $request->withoutVerifying())
                ->timeout(10)
                ->send('POST', $dispatch->webhook->url);

            if ($response->successful()) {
                $dispatch->markDelivered();
            } else {
                $dispatch->addFailure($response);
            }
        } catch (Throwable $e) {
            $dispatch->addFailure($e);
        }

        $dispatch->save();
    }
}
