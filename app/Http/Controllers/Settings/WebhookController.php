<?php


namespace App\Http\Controllers\Settings;


use App\Facades\Accounts;
use App\Models\Webhook;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookManager;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class WebhookController
{
    public function __construct(
        protected WebhookManager $webhooks,
    ) { }

    public function index()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $events = $this->webhooks->getAvailableEventDefinitions()
            ->groupBy(fn (WebhookDefinition $definition) => $definition->group->value)
            ->values()
            ->sortBy(fn (Collection $definitions) => Str::ascii($definitions->first()->group->label()))
            ->values()
            ->map(fn (Collection $definitions) => $definitions->sortBy('id')->values())
            ->map(fn (Collection $definitions) => [
                'group' => $definitions->first()->group->label(),
                'events' => $definitions->map(fn (WebhookDefinition $definition) => [
                    'id' => $definition->id,
                    'description' => $definition->description,
                ])->all()
            ])
            ->all();

        $webhooks = $account->webhooks;
        $webhooks->load(['events']);

        return Inertia::render('Settings/Webhooks', [
            'events' => $events,
            'webhooks' => $webhooks->map(fn (Webhook $webhook) => [
                'id' => $webhook->uuid,
                'name' => $webhook->name,
                'url' => $webhook->url,
                'active' => $webhook->active,
                'secret' => $webhook->secret,
                'events' => $webhook->events->pluck('event')->all(),
            ]),
        ]);
    }

    public function store(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $events = $this->webhooks->getAvailableEventDefinitions()->map(fn (WebhookDefinition $definition) => $definition->id);

        $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'url' => ['required', 'url:http,https', 'max:1000', Rule::unique(Webhook::class, 'url')->where('account_id', $account->id)],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', Rule::in($events), 'max:180', 'distinct'],
        ]);

        DB::transaction(function () use ($account, $request) {
            /** @var \App\Models\Webhook $webhook */
            $webhook = $account->webhooks()->create([
                'name' => $request->input('name'),
                'url' => $request->input('url'),
                'secret' => Str::random(40),
                'active' => true,
            ]);

            $webhook->events()->createMany(
                $request->collect('events')->map(fn (string $event) => ['event' => $event])
            );
        });

        return back();
    }

    public function update(Request $request, Webhook $webhook)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        abort_unless($account->is($webhook->account), 403);

        $events = $this->webhooks->getAvailableEventDefinitions()->map(fn (WebhookDefinition $definition) => $definition->id);

        $request->validate([
            'name' => ['required', 'string', 'max:180'],
            'url' => ['required', 'url:http,https', 'max:1000', Rule::unique(Webhook::class, 'url')->where('account_id', $account->id)->ignoreModel($webhook)],
            'events' => ['required', 'array', 'min:1'],
            'events.*' => ['string', Rule::in($events), 'max:180', 'distinct'],
        ]);

        DB::transaction(function () use ($webhook, $request) {
            $webhook->update([
                'name' => $request->input('name'),
                'url' => $request->input('url'),
            ]);

            $webhook->events()->delete();

            $webhook->events()->createMany(
                $request->collect('events')->map(fn (string $event) => ['event' => $event])
            );
        });

        return back();
    }

    public function destroy(Webhook $webhook)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        abort_unless($account->is($webhook->account), 403);

        DB::transaction(fn () => $webhook->delete());

        return back();
    }
}
