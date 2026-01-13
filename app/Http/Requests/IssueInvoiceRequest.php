<?php

namespace App\Http\Requests;

use Illuminate\Support\Facades\Gate;

class IssueInvoiceRequest extends UpdateInvoiceRequest
{
    public function authorize(): bool
    {
        $invoice = $this->invoice();

        if (! Gate::allows('update', $invoice)) {
            return false;
        }

        abort_unless($invoice->draft, 400, 'Only draft invoices can be issued');

        return true;
    }

    public function strict(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $rules = parent::rules();

        $rules['public_invoice_number'] = ['nullable', 'string', 'max:191'];

        return $rules;
    }
}
