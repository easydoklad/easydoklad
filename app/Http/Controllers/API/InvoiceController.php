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

            return $invoice;
        });

        return $invoice->toResource();
    }

    public function update(InvoiceRequest $request, Invoice $invoice)
    {
        // TODO: Logo
        // TODO: Signature

        abort_if($invoice->locked, 400, "The invoice is locked.");

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

        return $invoice->toResource();
    }
}
