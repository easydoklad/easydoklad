<?php

namespace App\Http\Controllers\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class DuplicateController
{
    public function __invoke(Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        abort_if($invoice->draft, 400, 'Draft invoices cannot be duplicated');

        $copy = DB::transaction(fn () => $invoice->duplicate());

        return to_route('invoices.show', $copy);
    }
}
