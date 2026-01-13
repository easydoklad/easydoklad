<?php

use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;

use function Pest\Laravel\deleteJson;

uses(RefreshDatabase::class);

it('should authorize to delete invoice', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create();

    deleteJson("api/v1/invoices/{$invoice->uuid}")->assertUnauthorized();
});

it('should not allow to delete invoice of other account', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create();

    actingAsSanctumAccount(createAccount())->deleteJson("api/v1/invoices/{$invoice->uuid}")->assertForbidden();
});

it('should delete invoice draft', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create(['draft' => true, 'locked' => false]);

    expect($account)->invoices->toHaveCount(1);

    actingAsSanctumAccount($account)
        ->deleteJson("api/v1/invoices/{$invoice->uuid}")
        ->assertNoContent();

    expect($account->refresh())->invoices->toBeEmpty();
});

it('should delete issued invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create(['draft' => false, 'locked' => false]);

    expect($account)->invoices->toHaveCount(1);

    actingAsSanctumAccount($account)
        ->deleteJson("api/v1/invoices/{$invoice->uuid}")
        ->assertNoContent();

    expect($account->refresh())->invoices->toBeEmpty();
});

it('should not allow to delete locked invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create(['draft' => false, 'locked' => true]);

    expect($account)->invoices->toHaveCount(1);

    actingAsSanctumAccount($account)
        ->deleteJson("api/v1/invoices/{$invoice->uuid}")
        ->assertBadRequest();

    expect($account->refresh())->invoices->toHaveCount(1);
});
