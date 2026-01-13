<?php


namespace App\Http\Controllers\API;


use App\Facades\Accounts;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Contracts\Cache\LockTimeoutException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class IssueInvoiceController extends Controller
{
    public function __invoke(Invoice $invoice)
    {
        abort_unless(Accounts::current()->is($invoice->account), 403);
        abort_unless($invoice->draft, 400, "The invoice is already issued.");

        $invoice->load([
            'supplier.address',
            'customer.address',
            'payments',
            'lines',
        ]);

        Validator::make(
            data: [
                'supplied_at' => $invoice->supplied_at?->format('Y-m-d'),
                'payment_due_to' => $invoice->payment_due_to?->format('Y-m-d'),
                'supplier_business_name' => $invoice->supplier->business_name,
                'supplier_address_line_one' => $invoice->supplier->address?->line_one,
                'supplier_address_city' => $invoice->supplier->address?->city,
                'supplier_address_country' => $invoice->supplier->address?->country,
                'customer_business_name' => $invoice->customer->business_name,
                'customer_address_line_one' => $invoice->customer->address?->line_one,
                'customer_address_city' => $invoice->customer->address?->city,
                'customer_address_country' => $invoice->customer->address?->country,
                'lines' => $invoice->lines->map(fn (InvoiceLine $line) => [
                    'title' => $line->title,
                    'unit_price' => $line->unit_price_vat_exclusive?->getMinorAmount()->toInt(),
                    'vat' => $line->vat_rate,
                    'total_vat_inclusive' => $line->total_price_vat_inclusive?->getMinorAmount()->toInt(),
                ])->all(),
            ],
            rules: [
                'supplied_at' => ['required'],
                'payment_due_to' => ['required'],
                'supplier_business_name' => ['required'],
                'supplier_address_line_one' => ['required'],
                'supplier_address_city' => ['required'],
                'supplier_address_country' => ['required'],
                'customer_business_name' => ['required'],
                'customer_address_line_one' => ['required'],
                'customer_address_city' => ['required'],
                'customer_address_country' => ['required'],
                'lines' => ['array', 'max:100', 'min:1'],
                'lines.*.title' => ['required', 'string', 'max:500'],
                'lines.*.unit_price' => [$invoice->vat_enabled ? 'required' : 'nullable'],
                'lines.*.vat' => [$invoice->vat_enabled ? 'required' : 'nullable'],
                'lines.*.total_vat_inclusive' => [$invoice->vat_enabled ? 'required' : 'nullable', 'integer'],
            ],
        )->validate();

        try {
            $invoice->whileLocked(fn () => DB::transaction(fn () => $invoice->issue()));
        } catch (LockTimeoutException) {
            abort(423, 'Nepodarilo sa vystaviť faktúru. Skúste to znovu.');
        }

        return $invoice->toResource();
    }
}
