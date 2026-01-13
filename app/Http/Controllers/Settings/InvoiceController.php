<?php

namespace App\Http\Controllers\Settings;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Facades\Accounts;
use App\Models\DocumentTemplate;
use App\Support\NumberSequenceFormatter;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use StackTrace\Ui\SelectOption;

class InvoiceController
{
    public function edit()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        /** @var \App\Models\NumberSequence|null $invoiceNumberSequence */
        $invoiceNumberSequence = $account
            ->numberSequences()
            ->firstWhere(
                'sequence_token',
                (new NumberSequenceFormatter($account->invoice_numbering_format, now()))->formatSequenceToken()
            );

        $templates = DocumentTemplate::ofType(DocumentType::Invoice)
            ->availableForAccount($account)
            ->get();

        return Inertia::render('Settings/Invoice', [
            'vatEnabled' => $account->vat_enabled,
            'numberingFormat' => $account->invoice_numbering_format,
            'variableSymbolFormat' => $account->invoice_variable_symbol_format,
            'nextNumber' => $invoiceNumberSequence ? $invoiceNumberSequence->next_number : 1,
            'defaultVatRate' => $account->default_vat_rate,
            'dueDays' => $account->invoice_due_days,
            'footerNote' => $account->invoice_footer_note,
            'template' => $account->invoiceTemplate->id,
            'templates' => $templates->map(fn (DocumentTemplate $template) => new SelectOption($template->name, $template->id)),
            'paymentMethod' => $account->invoice_payment_method->value,
            'paymentMethods' => PaymentMethod::options(),
            'signatureFileUrl' => $account->invoiceSignature?->url(),
            'logoFileUrl' => $account->invoiceLogo?->url(),
        ]);
    }

    public function update(Request $request)
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $request->validate([
            'numbering_format' => ['required', 'string', 'max:191'],
            'variable_symbol_format' => ['required', 'string', 'max:10'],
            'default_vat_rate' => ['required', 'numeric', 'max:99', 'min:0'],
            'due_days' => ['required', 'numeric', 'max:365', 'min:1'],
            'footer_note' => ['nullable', 'string', 'max:500'],
            'template' => ['required', 'integer', 'min:1', function (string $attribute, int $value, Closure $fail) use ($account) {
                if (DocumentTemplate::ofType(DocumentType::Invoice)->availableForAccount($account)->where('id', $value)->doesntExist()) {
                    $fail('Táto šablóna nie je dostupná.');
                }
            }],
            'payment_method' => ['required', 'string', Rule::enum(PaymentMethod::class)],
            'next_number' => ['required', 'numeric', 'min:1'],
        ]);

        $account->update([
            'invoice_numbering_format' => $request->input('numbering_format'),
            'invoice_variable_symbol_format' => $request->input('variable_symbol_format'),
            'default_vat_rate' => $request->input('default_vat_rate'),
            'invoice_due_days' => $request->input('due_days'),
            'invoice_footer_note' => $request->input('footer_note'),
            'invoice_template_id' => $request->input('template'),
            'invoice_payment_method' => $request->enum('payment_method', PaymentMethod::class),
        ]);

        $formatter = new NumberSequenceFormatter($account->invoice_numbering_format, now());
        $sequenceToken = $formatter->formatSequenceToken();

        /** @var \App\Models\NumberSequence|null $invoiceNumberSequence */
        $invoiceNumberSequence = $account->numberSequences()->firstWhere('sequence_token', $sequenceToken);

        $nextNumber = $request->integer('next_number');

        $currentNumber = $invoiceNumberSequence ? $invoiceNumberSequence->next_number : 1;
        if ($currentNumber != $nextNumber) {
            if ($invoiceNumberSequence) {
                $invoiceNumberSequence->next_number = $nextNumber;
                $invoiceNumberSequence->save();
            } else {
                $account->numberSequences()->create([
                    'format' => $formatter->getFormat(),
                    'next_number' => $nextNumber,
                    'sequence_token' => $sequenceToken,
                ]);
            }
        }

        return back();
    }
}
