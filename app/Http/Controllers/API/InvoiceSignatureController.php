<?php

namespace App\Http\Controllers\API;

use App\Facades\Accounts;
use App\Models\Invoice;

class InvoiceSignatureController
{
    public function show(Invoice $invoice)
    {
        abort_unless(Accounts::current()->is($invoice->account), 403);

        return response()->json([
            'data' => $invoice->signature ? [
                'image' => $invoice->signature->asBase64(),
            ] : null,
        ]);
    }
}
