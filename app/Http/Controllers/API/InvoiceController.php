<?php


namespace App\Http\Controllers\API;


use App\Enums\Country;
use App\Enums\PaymentMethod;
use App\Facades\Accounts;
use App\Http\Requests\API\InvoiceRequest;
use App\Models\Address;
use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use App\Support\Patch;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\DB;

class InvoiceController extends Controller
{
    public function index()
    {
        $invoices = Accounts::current()
            ->invoices()
            ->with([
                'supplier.address',
                'customer.address',
                'template',
                'lines',
                'numberSequence',
                'payments',
            ])
            ->orderByDesc('created_at');

        return $this->paginate($invoices)->toResourceCollection();
    }

    public function show(Invoice $invoice)
    {
        abort_unless(Accounts::current()->is($invoice->account), 403);

        $invoice->load([
            'supplier.address',
            'customer.address',
            'template',
            'lines',
            'numberSequence',
            'payments',
        ]);

        return $invoice->toResource();
    }

    public function store(InvoiceRequest $request)
    {
        // TODO: Logo
        // TODO: Signature
        // TODO: Issue

        $account = Accounts::current();

        $invoice = DB::transaction(function () use ($account, $request) {
            $invoice = new Invoice([
                'draft' => true,
                'sent' => false,
                'paid' => false,
                'locked' => false,
                'payment_method' => $account->invoice_payment_method,
                'currency' => $account->getCurrency()->getCurrencyCode(),
                'vat_enabled' => $account->vat_enabled,
                'template_id' => $account->invoiceTemplate->id,
                'footer_note' => $account->invoice_footer_note,
                'vat_reverse_charge' => false,
                'show_pay_by_square' => true,
                'issued_at' => now(),
                'supplied_at' => now(),
                'payment_due_to' => now()->addDays($account->invoice_due_days - 1),
            ]);

            $patch = new Patch($request->validated());
            $patch->fillOnly($invoice, [
                'invoice_number' => 'public_invoice_number',
                'issued_at' => fn (string $value) => Date::createFromFormat('Y-m-d', $value),
                'supplied_at' => fn (string $value) => Date::createFromFormat('Y-m-d', $value),
                'payment_due_to' => fn (string $value) => Date::createFromFormat('Y-m-d', $value),
                'vat_enabled',
                'vat_reverse_charge',
                'issued_by',
                'issued_by_phone_number',
                'issued_by_email',
                'issued_by_website',
                'payment_method' => fn (string $value) => PaymentMethod::from($value),
                'variable_symbol',
                'specific_symbol',
                'constant_symbol',
                'show_pay_by_square',
                'footer_note',
            ]);

            if ($templateUuid = $patch->get('template')) {
                $invoice->template()->associate(DocumentTemplate::findOrFailByUUID($templateUuid));
            }

            $supplier = $account->company->withoutRelations()->replicate(['address_id']);
            $patch->fillOnly($supplier, [
                'supplier_business_name' => 'business_name',
                'supplier_business_id' => 'business_id',
                'supplier_vat_id' => 'vat_id',
                'supplier_eu_vat_id' => 'eu_vat_id',
                'supplier_email' => 'email',
                'supplier_website' => 'website',
                'supplier_phone_number' => 'phone_number',
                'supplier_additional_info' => 'additional_info',
                'bank_name' => 'bank_name',
                'bank_address' => 'bank_address',
                'bank_bic' => 'bank_bic',
                'bank_account_number' => 'bank_account_number',
                'bank_account_iban' => 'bank_account_iban',
            ]);
            $supplierAddress = $account->company->address?->replicate() ?: new Address;
            $patch->fillOnly($supplierAddress, [
                'supplier_address_line_one' => 'line_one',
                'supplier_address_line_two' => 'line_two',
                'supplier_address_line_three' => 'line_three',
                'supplier_address_city' => 'city',
                'supplier_address_postal_code' => 'postal_code',
                'supplier_address_country' => fn (string $country) => ['country' => Country::from($country)],
            ]);
            $supplierAddress->save();
            $supplier->address()->associate($supplierAddress);
            $supplier->save();
            $invoice->supplier()->associate($supplier);

            $customer = new Company;
            $patch->fillOnly($customer, [
                'customer_business_name' => 'business_name',
                'customer_business_id' => 'business_id',
                'customer_vat_id' => 'vat_id',
                'customer_eu_vat_id' => 'eu_vat_id',
                'customer_email' => 'email',
                'customer_website' => 'website',
                'customer_phone_number' => 'phone_number',
                'customer_additional_info' => 'additional_info',
            ]);
            $customerAddress = new Address;
            $patch->fillOnly($customerAddress, [
                'customer_address_line_one' => 'line_one',
                'customer_address_line_two' => 'line_two',
                'customer_address_line_three' => 'line_three',
                'customer_address_city' => 'city',
                'customer_address_postal_code' => 'postal_code',
                'customer_address_country' => fn (string $country) => ['country' => Country::from($country)],
            ]);
            $customerAddress->save();
            $customer->address()->associate($customerAddress);
            $customer->save();
            $invoice->customer()->associate($customer);

            $invoice->account()->associate($account);

            $signature = $account->invoiceSignature?->replicate();
            $signature?->save();
            $invoice->signature()->associate($signature);

            $logo = $account->invoiceLogo?->replicate();
            $logo?->save();
            $invoice->logo()->associate($logo);

            $invoice->save();

            $request->collect('lines')->each(function (array $line, int $idx) use ($invoice) {
                $unitPrice = Arr::get($line, 'unit_price');
                $totalVatInclusive = Arr::get($line, 'total_vat_inclusive');
                $totalVatExclusive = Arr::get($line, 'total_vat_exclusive');

                $invoice->lines()->create([
                    'position' => $idx + 1,
                    'title' => Arr::get($line, 'title'),
                    'description' => Arr::get($line, 'description'),
                    'unit' => Arr::get($line, 'unit_of_measure'),
                    'quantity' => Arr::get($line, 'quantity'),
                    'vat_rate' => Arr::get($line, 'vat'),
                    'unit_price_vat_exclusive' => is_numeric($unitPrice) ? Money::ofMinor($unitPrice, $invoice->currency) : null,
                    'total_price_vat_inclusive' => is_numeric($totalVatInclusive) ? Money::ofMinor($totalVatInclusive, $invoice->currency) : null,
                    'total_price_vat_exclusive' => is_numeric($totalVatExclusive) ? Money::ofMinor($totalVatExclusive, $invoice->currency) : null,
                    'currency' => $invoice->currency,
                ]);
            });

            return $invoice;
        });

        return $invoice->toResource();
    }
}
