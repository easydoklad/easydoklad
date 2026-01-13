<?php

use App\Enums\Country;
use App\Enums\PaymentMethod;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Date;
use Illuminate\Testing\Fluent\AssertableJson;

use function Pest\Laravel\patchJson;

uses(RefreshDatabase::class);

it('should authorize to update an invoice', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create();

    patchJson("api/v1/invoices/{$invoice->uuid}", [
        'invoice_number' => 'FA001',
    ])->assertUnauthorized();
});

it('should not allow to update invoice of other account', function () {
    $invoice = Invoice::factory()
        ->for(createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create();

    actingAsSanctumAccount(createAccount())
        ->patchJson("api/v1/invoices/{$invoice->uuid}", [
            'invoice_number' => 'FA001',
        ])
        ->assertForbidden();
});

it('should not allow to update locked invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withCustomer()
        ->withSupplier()
        ->withDefaultTemplate()
        ->create(['draft' => false, 'locked' => true]);

    actingAsSanctumAccount($account)
        ->patchJson("api/v1/invoices/{$invoice->uuid}", [
            'invoice_number' => 'FA001',
        ])
        ->assertBadRequest();
});

it('should update an invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => true, 'locked' => false]);

    $template = DocumentTemplate::factory()->for($account)->create([
        'name' => 'Custom nazov',
        'description' => 'Custom popis',
    ]);

    actingAsSanctumAccount($account)
        ->patchJson("api/v1/invoices/{$invoice->uuid}", [
            'invoice_number' => 'FA001',
            'issued_at' => '2026-01-04',
            'supplied_at' => '2025-12-31',
            'payment_due_to' => '2026-01-14',

            'supplier_business_name' => 'Kominky s.r.o.',
            'supplier_business_id' => '50123456',
            'supplier_vat_id' => '212345678',
            'supplier_eu_vat_id' => 'SK212345678',
            'supplier_email' => 'info@kominky.sk',
            'supplier_phone_number' => '+421950123456',
            'supplier_website' => 'https://www.kominky.sk',
            'supplier_additional_info' => 'sme platcom dph',
            'supplier_address_line_one' => 'Kominkova 12',
            'supplier_address_line_two' => 'Vchod 3',
            'supplier_address_line_three' => 'Poschodie 4',
            'supplier_address_city' => 'Kominkov',
            'supplier_address_postal_code' => '08123',
            'supplier_address_country' => 'sk',

            'customer_business_name' => 'Rurky s.r.o.',
            'customer_business_id' => '60123456',
            'customer_vat_id' => '312345678',
            'customer_eu_vat_id' => 'SK312345678',
            'customer_email' => 'info@rurky.sk',
            'customer_phone_number' => '+421950654321',
            'customer_website' => 'https://www.rurky.sk',
            'customer_additional_info' => 'nemame licenciu',
            'customer_address_line_one' => 'Rurkova 20',
            'customer_address_line_two' => 'Vchod 12',
            'customer_address_line_three' => 'Poschodie 2',
            'customer_address_city' => 'Rurkov',
            'customer_address_postal_code' => '08321',
            'customer_address_country' => 'sk',

            'vat_enabled' => false,
            'vat_reverse_charge' => true,
            'issued_by' => 'Janko Hraško',
            'issued_by_phone_number' => '+421950123321',
            'issued_by_email' => 'janko@hraskoo.sk',
            'issued_by_website' => 'https://www.hraskoo.sk',
            'payment_method' => 'bank-transfer',
            'bank_name' => 'Hraškobanka a.s.',
            'bank_address' => 'Strukova 14, 08123 Hraškovce',
            'bank_bic' => 'PEASX',
            'bank_account_number' => '123456',
            'bank_account_iban' => 'PE1212341234',
            'variable_symbol' => '123456',
            'specific_symbol' => '654321',
            'constant_symbol' => '1234',
            'show_pay_by_square' => false,
            'footer_note' => 'This is footer note',

            'template' => $template->uuid,

            'lines' => [
                [
                    'title' => 'Kominok',
                    'description' => 'Mala veľkosť',
                    'quantity' => 2,
                    'unit_of_measure' => 'ks',
                    'unit_price' => 10000,
                    'vat' => null,
                    'total_vat_exclusive' => 20000,
                    'total_vat_inclusive' => null,
                ],
                [
                    'title' => 'Kalapik',
                    'description' => 'Stredna veľkosť',
                    'quantity' => 3,
                    'unit_of_measure' => 'ks',
                    'unit_price' => 5000,
                    'vat' => null,
                    'total_vat_exclusive' => 15000,
                    'total_vat_inclusive' => null,
                ],
            ],
        ])
        ->assertSuccessful()
        ->assertJson(fn (AssertableJson $json) => $json->has('data', fn (AssertableJson $json) => $json->has('id')
            ->where('is_draft', true)
            ->where('issued_at', '2026-01-04')
            ->where('supplied_at', '2025-12-31')
            ->where('payment_due_to', '2026-01-14')
            ->where('supplier.business_name', 'Kominky s.r.o.')
            ->where('supplier.business_id', '50123456')
            ->where('supplier.vat_id', '212345678')
            ->where('supplier.eu_vat_id', 'SK212345678')
            ->where('supplier.email', 'info@kominky.sk')
            ->where('supplier.phone_number', '+421950123456')
            ->where('supplier.website', 'https://www.kominky.sk')
            ->where('supplier.additional_info', 'sme platcom dph')
            ->where('supplier.address.line_one', 'Kominkova 12')
            ->where('supplier.address.line_two', 'Vchod 3')
            ->where('supplier.address.line_three', 'Poschodie 4')
            ->where('supplier.address.city', 'Kominkov')
            ->where('supplier.address.postal_code', '08123')
            ->where('supplier.address.country.code', 'sk')
            ->where('customer.business_name', 'Rurky s.r.o.')
            ->where('customer.business_id', '60123456')
            ->where('customer.vat_id', '312345678')
            ->where('customer.eu_vat_id', 'SK312345678')
            ->where('customer.email', 'info@rurky.sk')
            ->where('customer.phone_number', '+421950654321')
            ->where('customer.website', 'https://www.rurky.sk')
            ->where('customer.additional_info', 'nemame licenciu')
            ->where('customer.address.line_one', 'Rurkova 20')
            ->where('customer.address.line_two', 'Vchod 12')
            ->where('customer.address.line_three', 'Poschodie 2')
            ->where('customer.address.city', 'Rurkov')
            ->where('customer.address.postal_code', '08321')
            ->where('customer.address.country.code', 'sk')
            ->where('vat_enabled', false)
            ->where('vat_reverse_charge', true)
            ->where('issued_by', 'Janko Hraško')
            ->where('issued_by_phone_number', '+421950123321')
            ->where('issued_by_email', 'janko@hraskoo.sk')
            ->where('issued_by_website', 'https://www.hraskoo.sk')
            ->where('payment_method', 'bank-transfer')
            ->where('bank_transfer_info.bank_name', 'Hraškobanka a.s.')
            ->where('bank_transfer_info.bank_address', 'Strukova 14, 08123 Hraškovce')
            ->where('bank_transfer_info.bank_bic', 'PEASX')
            ->where('bank_transfer_info.bank_account_number', '123456')
            ->where('bank_transfer_info.bank_account_iban', 'PE1212341234')
            ->where('variable_symbol', '123456')
            ->where('specific_symbol', '654321')
            ->where('constant_symbol', '1234')
            ->where('show_pay_by_square', false)
            ->where('footer_note', 'This is footer note')
            ->where('template.id', $template->uuid)
            ->where('template.name', 'Custom nazov')
            ->where('template.description', 'Custom popis')
            ->where('lines.0.title', 'Kominok')
            ->where('lines.0.description', 'Mala veľkosť')
            ->where('lines.0.position', 1)
            ->where('lines.0.unit_of_measure', 'ks')
            ->where('lines.0.quantity', 2)
            ->where('lines.0.vat_rate', null)
            ->where('lines.0.currency', 'EUR')
            ->where('lines.0.unit_price_vat_exclusive', 10000)
            ->where('lines.0.total_price_vat_exclusive', 20000)
            ->where('lines.0.total_price_vat_inclusive', null)
            ->where('lines.0.vat_amount', null)
            ->where('lines.1.title', 'Kalapik')
            ->where('lines.1.description', 'Stredna veľkosť')
            ->where('lines.1.position', 2)
            ->where('lines.1.unit_of_measure', 'ks')
            ->where('lines.1.quantity', 3)
            ->where('lines.1.vat_rate', null)
            ->where('lines.1.currency', 'EUR')
            ->where('lines.1.unit_price_vat_exclusive', 5000)
            ->where('lines.1.total_price_vat_exclusive', 15000)
            ->where('lines.1.total_price_vat_inclusive', null)
            ->where('lines.1.vat_amount', null)
            ->etc()
        )
        );

    $invoice->refresh();

    expect($invoice)
        ->issued_at->toDateString()->toBe('2026-01-04')
        ->supplied_at->toDateString()->toBe('2025-12-31')
        ->payment_due_to->toDateString()->toBe('2026-01-14')
        ->vat_enabled->toBeFalse()
        ->vat_reverse_charge->toBeTrue()
        ->issued_by->toBe('Janko Hraško')
        ->issued_by_phone_number->toBe('+421950123321')
        ->issued_by_email->toBe('janko@hraskoo.sk')
        ->issued_by_website->toBe('https://www.hraskoo.sk')
        ->payment_method->toBe(PaymentMethod::BankTransfer)
        ->variable_symbol->toBe('123456')
        ->specific_symbol->toBe('654321')
        ->constant_symbol->toBe('1234')
        ->show_pay_by_square->toBeFalse()
        ->footer_note->toBe('This is footer note')
        ->and($invoice->supplier)
        ->bank_name->toBe('Hraškobanka a.s.')
        ->bank_address->toBe('Strukova 14, 08123 Hraškovce')
        ->bank_bic->toBe('PEASX')
        ->bank_account_number->toBe('123456')
        ->bank_account_iban->toBe('PE1212341234')
        ->business_name->toBe('Kominky s.r.o.')
        ->business_id->toBe('50123456')
        ->vat_id->toBe('212345678')
        ->eu_vat_id->toBe('SK212345678')
        ->email->toBe('info@kominky.sk')
        ->phone_number->toBe('+421950123456')
        ->website->toBe('https://www.kominky.sk')
        ->additional_info->toBe('sme platcom dph')
        ->and($invoice->supplier->address)
        ->line_one->toBe('Kominkova 12')
        ->line_two->toBe('Vchod 3')
        ->line_three->toBe('Poschodie 4')
        ->city->toBe('Kominkov')
        ->postal_code->toBe('08123')
        ->country->toBe(Country::Slovakia)
        ->and($invoice->customer)
        ->business_name->toBe('Rurky s.r.o.')
        ->business_id->toBe('60123456')
        ->vat_id->toBe('312345678')
        ->eu_vat_id->toBe('SK312345678')
        ->email->toBe('info@rurky.sk')
        ->phone_number->toBe('+421950654321')
        ->website->toBe('https://www.rurky.sk')
        ->additional_info->toBe('nemame licenciu')
        ->and($invoice->customer->address)
        ->line_one->toBe('Rurkova 20')
        ->line_two->toBe('Vchod 12')
        ->line_three->toBe('Poschodie 2')
        ->city->toBe('Rurkov')
        ->postal_code->toBe('08321')
        ->country->toBe(Country::Slovakia)
        ->and($invoice)->lines->toHaveCount(2)
        ->and($invoice->lines[0])
        ->title->toBe('Kominok')
        ->description->toBe('Mala veľkosť')
        ->position->toBe(1)
        ->unit->toBe('ks')
        ->quantity->toBe(2.0)
        ->vat_rate->toBeNull()
        ->currency->toBe('EUR')
        ->unit_price_vat_exclusive->toBeMoney(10000)
        ->total_price_vat_exclusive->toBeMoney(20000)
        ->total_price_vat_inclusive->toBeNull()
        ->getVatAmount()->toBeNull()
        ->and($invoice->lines[1])
        ->title->toBe('Kalapik')
        ->description->toBe('Stredna veľkosť')
        ->position->toBe(2)
        ->unit->toBe('ks')
        ->quantity->toBe(3.0)
        ->vat_rate->toBeNull()
        ->currency->toBe('EUR')
        ->unit_price_vat_exclusive->toBeMoney(5000)
        ->total_price_vat_exclusive->toBeMoney(15000)
        ->total_price_vat_inclusive->toBeNull()
        ->getVatAmount()->toBeNull();
});

it('should allow partially update invoice', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create([
            'draft' => true,
            'locked' => false,
            'issued_at' => Date::createFromFormat('Y-m-d', '2026-01-13'),
            'payment_due_to' => Date::createFromFormat('Y-m-d', '2026-01-20'),
        ]);

    expect($invoice)
        ->issued_at->toDateString()->toBe('2026-01-13')
        ->payment_due_to->toDateString()->toBe('2026-01-20');

    actingAsSanctumAccount($account)
        ->patchJson("api/v1/invoices/{$invoice->uuid}", [
            'payment_due_to' => '2026-01-30',
        ])
        ->assertSuccessful();

    expect($invoice->refresh())
        ->issued_at->toDateString()->toBe('2026-01-13')
        ->payment_due_to->toDateString()->toBe('2026-01-30');
});

it('should not allow to unset required data when invoice is already issued', function () {
    $invoice = Invoice::factory()
        ->for($account = createAccount())
        ->withSupplier()
        ->withCustomer()
        ->withDefaultTemplate()
        ->withLines()
        ->create(['draft' => false, 'locked' => false]);

    actingAsSanctumAccount($account)
        ->patchJson("api/v1/invoices/{$invoice->uuid}", [
            'issued_at' => null,
            'supplied_at' => null,
            'payment_due_to' => null,
            'supplier_business_name' => null,
            'supplier_address_line_one' => null,
            'supplier_address_city' => null,
            'supplier_address_country' => null,
            'customer_business_name' => null,
            'customer_address_line_one' => null,
            'customer_address_city' => null,
            'customer_address_country' => null,
            'lines' => [],
        ])
        ->assertJsonValidationErrors([
            'issued_at',
            'supplied_at',
            'payment_due_to',
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
