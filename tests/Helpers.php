<?php

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Enums\UserAccountRole;
use App\Models\Account;
use App\Models\Address;
use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Models\User;
use Laravel\Sanctum\Sanctum;

function createAccount(): Account
{
    $template = DocumentTemplate::query()
        ->where('is_default', true)
        ->whereNull('account_id')
        ->firstOrFail();

    return Account::factory()
        ->for(
            Company::factory()
                ->for(
                    Address::factory()->create([
                        'line_one' => 'Rastislavova 2151',
                        'postal_code' => '093 02',
                        'city' => 'Hencovce',
                        'country' => 'sk',
                    ])
                )
                ->create([
                    'business_name' => 'StackTrace s.r.o.',
                    'business_id' => '53736630',
                    'vat_id' => '2121479052',
                    'eu_vat_id' => 'SK2121479052',
                    'email' => 'info@stacktrace.sk',
                    'website' => 'https://www.stacktrace.sk',
                    'phone_number' => '+421 950 498 911',
                    'bank_name' => 'Tatra Banka a.s.',
                    'bank_address' => 'Hodžovo námestie 3, 811 06 Bratislava 1',
                    'bank_bic' => 'TATRSKBX',
                    'bank_account_iban' => 'SK88 1100 0000 0029 4510 2347',
                ])
        )
        ->create([
            'vat_enabled' => true,
            'default_vat_rate' => 23,
            'invoice_payment_method' => PaymentMethod::BankTransfer,
            'invoice_footer_note' => 'Spoločnosť je zapísaná v obchodnom registri Okresného súdu Prešov, oddiel: Sro, vložka č. 42064/P',
            'invoice_template_id' => $template->id,
        ]);
}

function getDefaultTemplate(DocumentType $type): DocumentTemplate
{
    return DocumentTemplate::query()->whereNull('account_id')->where('document_type', $type)->firstOrFail();
}

/**
 * @return mixed|\Pest\Concerns\Expectable|\Pest\PendingCalls\TestCall|\Pest\Support\HigherOrderTapProxy|\Tests\TestCase
 */
function actingAsSanctumAccount(?Account $account = null, $abilities = [], string $guard = 'api')
{
    $account = $account ?: createAccount();

    $token = Mockery::mock(Sanctum::personalAccessTokenModel())->shouldIgnoreMissing(false);

    if (in_array('*', $abilities)) {
        $token->shouldReceive('can')->withAnyArgs()->andReturn(true);
    } else {
        foreach ($abilities as $ability) {
            $token->shouldReceive('can')->with($ability)->andReturn(true);
        }
    }

    $account->withAccessToken($token);

    if (isset($account->wasRecentlyCreated) && $account->wasRecentlyCreated) {
        $account->wasRecentlyCreated = false;
    }

    $auth = app('auth')->guard($guard);

    // Account does not implement Authenticatable interface, hence we cannot call setUser on the guard.
    (new ReflectionClass($auth))->getProperty('user')->setValue($auth, $account);

    app('auth')->shouldUse($guard);

    return test();
}

/**
 * Authenticate a user on a selected account using the web guard.
 *
 * @return mixed|\Pest\Concerns\Expectable|\Pest\PendingCalls\TestCall|\Pest\Support\HigherOrderTapProxy|\Tests\TestCase
 */
function actingAsAccount(
    ?User $user = null,
    ?Account $account = null,
    ?UserAccountRole $role = null,
) {
    $user = $user ?: User::factory()->create();
    $account = $account ?: createAccount();
    $role = $role ?: UserAccountRole::Owner;

    $account->users()->attach($user, [
        'role' => $role->value,
    ]);

    return test()
        ->actingAs($user, 'web')
        ->withSession([
            'account' => $account->id,
        ]);
}
