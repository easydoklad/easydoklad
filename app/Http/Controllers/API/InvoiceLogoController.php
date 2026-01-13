<?php

namespace App\Http\Controllers\API;

use App\Facades\Accounts;
use App\Models\Invoice;

class InvoiceLogoController
{
    public function show(Invoice $invoice)
    {
        abort_unless(Accounts::current()->is($invoice->account), 403);

        return response()->json([
            'data' => $invoice->logo ? [
                'image' => $invoice->logo->asBase64(),
            ] : null,
        ]);
    }
}
