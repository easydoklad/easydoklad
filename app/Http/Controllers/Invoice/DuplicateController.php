<?php

namespace App\Http\Controllers\Invoice;

use App\Facades\Webhook;
use App\Models\Invoice;
use App\Webhooks\Events\InvoiceCreated;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DuplicateController
{
    public function __invoke(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        abort_if($invoice->draft, 400, 'Draft invoices cannot be duplicated');

        $copy = DB::transaction(fn () => $invoice->duplicate());

        Webhook::dispatch($invoice->account, new InvoiceCreated($copy));

        return to_route('invoices.show', $copy);
    }
}
