<?php

namespace App\Jobs;

use App\Models\DispatchedWebhook;
use App\Webhooks\WebhookManager;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class SendDispatchedWebhook implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public DispatchedWebhook $dispatch
    ) { }

    public function handle(WebhookManager $webhooks): void
    {
        $webhooks->sendWhileLocked($this->dispatch);
    }
}
