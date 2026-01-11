<?php


namespace App\Http\Controllers\API;


use App\Facades\Accounts;
use App\Models\Invoice;

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
}
