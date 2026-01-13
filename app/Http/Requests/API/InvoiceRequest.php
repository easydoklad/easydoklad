<?php


namespace App\Http\Requests\API;


use App\Enums\Country;
use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Facades\Accounts;
use App\Models\DocumentTemplate;
use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InvoiceRequest extends FormRequest
{
    public function rules(): array
    {
        $strict = $this->strict();
        $vatEnabled = $this->boolean('vat_enabled', Accounts::current()->vat_enabled);

        return [
            'invoice_number' => ['sometimes', $strict ? 'required' : 'nullable', 'string', 'max:191'],
            'issued_at' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'supplied_at' => ['sometimes', 'required', 'date_format:Y-m-d'],
            'payment_due_to' => ['sometimes', 'required', 'date_format:Y-m-d'],

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

            'template' => ['sometimes', 'required', 'string', 'min:1', function (string $attribute, string $value, Closure $fail) {
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

    public function strict(): bool
    {
        // TODO: WIP

        return true;
    }
}
