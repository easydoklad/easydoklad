<?php

namespace App\Http\Controllers\API;

use App\Facades\Accounts;
use App\Facades\Webhook;
use App\Http\Requests\API\InvoiceRequest;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use App\Webhooks\Events\InvoiceCreated;
use App\Webhooks\Events\InvoiceDeleted;
use App\Webhooks\Events\InvoiceIssued;
use App\Webhooks\Events\InvoiceUpdated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

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
            $invoice = Invoice::makeDraftForAccount($account);

            // Signature
            $signature = $account->invoiceSignature?->replicate();
            $signature?->save();
            $invoice->signature()->associate($signature);

            // Logo
            $logo = $account->invoiceLogo?->replicate();
            $logo?->save();
            $invoice->logo()->associate($logo);

            // Supplier
            $supplier = $account->company->withoutRelations()->replicate(['address_id']);
            $supplier->address()->associate($account->company->address?->replicate());
            $request->updateSupplier($supplier);
            $invoice->supplier()->associate($supplier);

            // Customer
            $customer = new Company;
            $request->updateCustomer($customer);
            $invoice->customer()->associate($customer);

            // Invoice
            $invoice->account()->associate($account);
            $request->updateInvoice($invoice);

            // Lines
            $request->replaceLines($invoice);

            // Refresh relations
            $invoice->load([
                'customer.address',
                'supplier.address',
                'payments',
                'lines',
            ]);

            // Calculate totals
            $invoice->calculateTotals();

            // When issuing invoice, any missing data will roll back the transaction and no invoice will be created.
            if ($request->boolean('issue')) {
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

                $invoice->issue();
            }

            return $invoice;
        });

        Webhook::dispatch($invoice->account, new InvoiceCreated($invoice));

        if (!$invoice->draft) {
            Webhook::dispatch($invoice->account, new InvoiceIssued($invoice));
        }

        return $invoice->toResource();
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        // TODO: Logo
        // TODO: Signature

        abort_if($invoice->locked, 400, 'The invoice is locked.');

        $invoice->load([
            'customer.address',
            'supplier.address',
            'lines',
        ]);

        DB::transaction(function () use ($request, $invoice) {
            // Supplier
            $request->updateSupplier($invoice->supplier);

            // Customer
            $request->updateCustomer($invoice->customer);

            // Invoice
            $request->updateInvoice($invoice);

            // Lines
            $request->replaceLines($invoice);

            // Refresh relations
            $invoice->load([
                'customer.address',
                'supplier.address',
                'payments',
                'lines',
            ]);

            // Calculate totals
            $invoice->calculateTotals();
        });

        Webhook::dispatch($invoice->account, new InvoiceUpdated($invoice));

        return $invoice->toResource();
    }

    public function destroy(Invoice $invoice)
    {
        abort_unless(Accounts::current()->is($invoice->account), 403);
        abort_if($invoice->locked, 400, 'The invoice is locked.');

        DB::transaction(fn () => $invoice->delete());

        Webhook::dispatch($invoice->account, new InvoiceDeleted($invoice));

        return response()->noContent();
    }
}
