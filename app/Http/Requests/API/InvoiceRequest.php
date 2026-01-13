<?php


namespace App\Http\Requests\API;


use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Facades\Accounts;
use App\Models\Address;
use App\Models\Company;
use App\Models\DocumentTemplate;
use App\Models\Invoice;
use App\Support\Patch;
use Brick\Money\Money;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Date;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        if ($invoice = $this->invoice()) {
            return Accounts::current()->is($invoice->account);
        }

        return true;
    }

    public function rules(): array
    {
        $strict = $this->strict();
        $vatEnabled = $this->vatEnabled();

        return [
            'issue' => ['sometimes', 'boolean'],

            'invoice_number' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'issued_at' => ['sometimes', $strict ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'supplied_at' => ['sometimes', $strict ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'payment_due_to' => ['sometimes', $strict ? 'required' : 'nullable', 'date_format:Y-m-d'],

            'supplier_business_name' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'supplier_business_id' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_vat_id' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_eu_vat_id' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_email' => ['sometimes', 'nullable', 'string', 'max:191', 'email'],
            'supplier_phone_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_website' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_additional_info' => ['sometimes', 'nullable', 'string', 'max:500'],
            'supplier_address_line_one' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'supplier_address_line_two' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_address_line_three' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_address_city' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'supplier_address_postal_code' => ['sometimes', 'nullable', 'string', 'max:191'],
            'supplier_address_country' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:2', Rule::enum(Country::class)],

            'customer_business_name' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'customer_business_id' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_vat_id' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_eu_vat_id' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_email' => ['sometimes', 'nullable', 'string', 'max:191', 'email'],
            'customer_phone_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_website' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_additional_info' => ['sometimes', 'nullable', 'string', 'max:500'],
            'customer_address_line_one' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'customer_address_line_two' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_address_line_three' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_address_city' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'customer_address_postal_code' => ['sometimes', 'nullable', 'string', 'max:191'],
            'customer_address_country' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:2', Rule::enum(Country::class)],

            'vat_enabled' => ['sometimes', 'boolean'],
            'vat_reverse_charge' => ['sometimes', 'boolean'],

            'footer_note' => ['sometimes', 'nullable', 'string', 'max:1000'],

            'issued_by' => ['sometimes', 'nullable', 'string', 'max:191'],
            'issued_by_email' => ['sometimes', 'nullable', 'string', 'max:191', 'email'],
            'issued_by_phone_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'issued_by_website' => ['sometimes', 'nullable', 'string', 'max:191'],

            'payment_method' => ['sometimes', 'required', 'string', 'max:191', Rule::enum(PaymentMethod::class)],
            'bank_name' => ['sometimes', 'nullable', 'string', 'max:191'],
            'bank_address' => ['sometimes', 'nullable', 'string', 'max:191'],
            'bank_bic' => ['sometimes', 'nullable', 'string', 'max:191'],
            'bank_account_number' => ['sometimes', 'nullable', 'string', 'max:191'],
            'bank_account_iban' => ['sometimes', 'nullable', 'string', 'max:191'],
            'variable_symbol' => ['sometimes', 'nullable', 'integer', 'max_digits:10'],
            'specific_symbol' => ['sometimes', 'nullable', 'integer', 'max_digits:10'],
            'constant_symbol' => ['sometimes', 'nullable', 'integer', 'max_digits:4'],

            'show_pay_by_square' => ['sometimes', 'boolean'],

            'template' => ['sometimes', 'required', 'uuid', function (string $attribute, string $value, Closure $fail) {
                $account = Accounts::current();

                if (DocumentTemplate::ofType(DocumentType::Invoice)->availableForAccount($account)->where('uuid', $value)->doesntExist()) {
                    $fail("Táto šablóna nie je dostupná.");
                }
            }],

            'lines' => ['sometimes', 'array', 'max:100', $strict ? 'min:1' : 'min:0'],
            'lines.*.title' => [$strict ? 'required' : 'nullable', 'string', 'max:500'],
            'lines.*.description' => ['nullable', 'string', 'max:1000'],
            'lines.*.quantity' => ['nullable', 'numeric'],
            'lines.*.unit_of_measure' => ['nullable', 'string', 'max:191'],
            'lines.*.unit_price' => [$strict && $vatEnabled ? 'required' : 'nullable', 'integer'],
            'lines.*.vat' => [$strict && $vatEnabled ? 'required' : 'nullable', 'numeric', 'min:0', 'max:100'],
            'lines.*.total_vat_exclusive' => ['nullable', 'integer'],
            'lines.*.total_vat_inclusive' => [$strict && $vatEnabled ? 'required' : 'nullable', 'integer'],
        ];
    }

    /**
     * Determine whether VAT is enabled on the invoice which is being modified.
     */
    public function vatEnabled(): bool
    {
        if ($this->has('vat_enabled')) {
            return $this->boolean('vat_enabled');
        }

        if ($invoice = $this->invoice()) {
            return $invoice->vat_enabled;
        }

        return Accounts::current()->vat_enabled;
    }

    /**
     * Determine if strict validation rules should apply for invoice under modification.
     */
    public function strict(): bool
    {
        if ($invoice = $this->invoice()) {
            return !$invoice->draft;
        }

        if ($this->has('issue')) {
            return $this->boolean('issue');
        }

        return false;
    }

    /**
     * Get the invoice being modified.
     */
    protected function invoice(): ?Invoice
    {
        if (($invoice = $this->route('invoice')) && $invoice instanceof Invoice) {
            return $invoice;
        }

        return null;
    }

    /**
     * Create a Patch instance.
     */
    protected function patch(): Patch
    {
        return new Patch($this->validated());
    }

    /**
     * Update given invoice from request data.
     */
    public function updateInvoice(Invoice $invoice): void
    {
        $patch = $this->patch();

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

        $invoice->save();
    }

    /**
     * Update supplier information.
     */
    public function updateSupplier(Company $supplier): void
    {
        $patch = $this->patch();

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

        $address = $supplier->address ?: new Address;

        $patch->fillOnly($address, [
            'supplier_address_line_one' => 'line_one',
            'supplier_address_line_two' => 'line_two',
            'supplier_address_line_three' => 'line_three',
            'supplier_address_city' => 'city',
            'supplier_address_postal_code' => 'postal_code',
            'supplier_address_country' => fn (string $country) => ['country' => Country::from($country)],
        ]);

        $address->save();

        $supplier->address()->associate($address);

        $supplier->save();
    }

    /**
     * Update customer information.
     */
    public function updateCustomer(Company $customer): void
    {
        $patch = $this->patch();

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

        $address = $customer->address ?: new Address;

        $patch->fillOnly($address, [
            'customer_address_line_one' => 'line_one',
            'customer_address_line_two' => 'line_two',
            'customer_address_line_three' => 'line_three',
            'customer_address_city' => 'city',
            'customer_address_postal_code' => 'postal_code',
            'customer_address_country' => fn (string $country) => ['country' => Country::from($country)],
        ]);

        $address->save();

        $customer->address()->associate($address);

        $customer->save();
    }

    /**
     * Replace lines if they are present on the invoice.
     */
    public function replaceLines(Invoice $invoice): void
    {
        $patch = $this->patch();

        if ($lines = $patch->get('lines')) {
            $invoice->lines->each->delete();

            collect($lines)->each(function (array $line, int $idx) use ($invoice) {
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
        }
    }
}
