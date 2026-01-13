<?php

use App\Models\Address;
use App\Models\Company;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('should authorize to issue invoice', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => true, 'locked' => false]);

    postJson("api/v1/invoices/{$invoice->uuid}/issue")
        ->assertUnauthorized();
});

it('should not allow to issue invoice of other account', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => true, 'locked' => false]);

    actingAsSanctumAccount(createAccount())
        ->postJson("api/v1/invoices/{$invoice->uuid}/issue")
        ->assertForbidden();
});

it('should issue an invoice', function () {
    Carbon::setTestNow('2026-01-01 20:00:00');

    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create([
            'draft' => true,
            'locked' => false,
            'issued_at' => null,
        ]);

    expect($invoice)
        ->issued_at->toBeNull()
        ->public_invoice_number->toBeNull()
        ->draft->toBeTrue()
        ->locked->toBeFalse();

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/issue")
        ->assertSuccessful();

    expect($invoice->refresh());

    expect($invoice)
        ->issued_at->not->toBeNull()
        ->issued_at->toDateString()->toBe('2026-01-01')
        ->public_invoice_number->not->toBeNull()
        ->draft->toBeFalse()
        ->locked->toBeTrue();
});

it('should not allow to issue already issued invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false, 'locked' => false]);

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/issue")
        ->assertBadRequest();
});

it('should require certain fields to be set when issuing invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->for(
            Company::factory()
                ->for(Address::factory()->create([
                    'line_one' => null,
                    'city' => null,
                    'country' => null,
                ]))
                ->create([
                    'business_name' => null,
                ]),
            'supplier'
        )
        ->for(
            Company::factory()
                ->for(Address::factory()->create([
                    'line_one' => null,
                    'city' => null,
                    'country' => null,
                ]))
                ->create([
                    'business_name' => null,
                ]),
            'customer'
        )
        ->withDefaultTemplate()
        ->create(['draft' => true, 'locked' => false]);

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/issue")
        ->assertJsonValidationErrors([
            'supplier_business_name',
            'supplier_address_line_one',
            'supplier_address_city',
            'supplier_address_country',
            'customer_business_name',
            'customer_address_line_one',
            'customer_address_city',
            'customer_address_country',
            'lines',
        ]);
});
