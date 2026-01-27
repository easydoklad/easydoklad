<?php

namespace App\Http\Controllers\Settings;

use App\Facades\Accounts;
use App\Models\Webhook;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use StackTrace\Ui\Facades\Toast;

class ToggleWebhookController
{
    public function __invoke(Request $request, Webhook $webhook)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        abort_unless($account->is($webhook->account), 403);

        $request->validate([
            'active' => ['required', 'boolean'],
        ]);

        $active = $request->boolean('active');

        DB::transaction(fn () => $webhook->update(['active' => $active]));

        Toast::make(
            $active ? 'Webhook bol aktivovaný.' : 'Webhook bol deaktivovaný.',
            $webhook->name
        );

        return back();
    }
}
