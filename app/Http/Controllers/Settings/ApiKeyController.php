<?php


namespace App\Http\Controllers\Settings;


use App\Facades\Accounts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Inertia\Inertia;
use Laravel\Sanctum\PersonalAccessToken;

class ApiKeyController
{
    public function index()
    {
        $account = Accounts::current();

        return Inertia::render('Settings/ApiKeys', [
            'apiKeys' => $account->tokens->map(fn (PersonalAccessToken $token) => [
                'id' => $token->id,
                'name' => $token->name,
                'expiresAt' => $token->expires_at?->format('d.m.Y'),
            ]),
            'minExpirationDate' => now()->addDay()->format('Y-m-d'),
            'maxExpirationDate' => now()->addYear()->format('Y-m-d'),
        ]);
    }

    public function store(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $minExpirationDate = now()->addDay()->format('Y-m-d');
        $maxExpirationDate = now()->addYear()->format('Y-m-d');

        $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'expires_at' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:'.$minExpirationDate, 'before_or_equal:'.$maxExpirationDate],
        ]);

        $expiresAt = $request->date('expires_at', 'Y-m-d')?->endOfDay();

        $token = $account->createToken($request->input('name'), expiresAt: $expiresAt);

        Inertia::flash('recentlyCreatedApiKey', [
            'token' => $token->plainTextToken,
        ]);

        return back();
    }

    public function destroy(PersonalAccessToken $token)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        abort_unless($account->is($token->tokenable), 403);

        DB::transaction(fn () => $token->delete());

        return back();
    }
}
