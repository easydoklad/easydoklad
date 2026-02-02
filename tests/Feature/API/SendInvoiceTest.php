<?php

use App\Mail\InvoiceMail;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;

use function Pest\Laravel\postJson;

uses(RefreshDatabase::class);

it('should require authentication to send invoice', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false]);

    postJson("api/v1/invoices/{$invoice->uuid}/send", [
        'email' => 'client@example.com',
    ])->assertUnauthorized();
});

it('should not allow to send invoice of other account', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false]);

    actingAsSanctumAccount(createAccount())
        ->postJson("api/v1/invoices/{$invoice->uuid}/send", [
            'email' => 'client@example.com',
        ])
        ->assertForbidden();
});

it('should not allow to send draft invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => true]);

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/send", [
            'email' => 'client@example.com',
        ])
        ->assertBadRequest();
});

it('should send invoice with email only', function () {
    Mail::fake();

    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false, 'sent' => false]);

    $email = 'client@example.com';

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/send", [
            'email' => $email,
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.is_sent', true);

    expect($invoice->refresh())
        ->sent->toBeTrue();

    Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->invoice->is($invoice));
});

it('should send invoice with message and locale', function () {
    Mail::fake();

    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false, 'sent' => false]);

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/send", [
            'email' => 'client@example.com',
            'message' => 'Custom message',
            'locale' => 'sk',
        ])
        ->assertSuccessful()
        ->assertJsonPath('data.is_sent', true);

    expect($invoice->refresh())->sent->toBeTrue();

    Mail::assertSent(InvoiceMail::class, fn (InvoiceMail $mail) => $mail->invoice->is($invoice));
});

it('should require email when sending invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false]);

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/send", [])
        ->assertJsonValidationErrors(['email']);
});

it('should validate locale when provided', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false]);

    actingAsSanctumAccount($account)
        ->postJson("api/v1/invoices/{$invoice->uuid}/send", [
            'email' => 'client@example.com',
            'locale' => 'xx',
        ])
        ->assertJsonValidationErrors(['locale']);
});
