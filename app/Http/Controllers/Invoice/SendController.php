<?php

namespace App\Http\Controllers\Invoice;

use App\Models\Invoice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class SendController
{
    public function __invoke(Request $request, Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        abort_if($invoice->draft, 400, 'Draft invoices cannot be sent');

        $request->validate([
            'email' => ['required', 'string', 'max:191', 'email'],
            'message' => ['required', 'string', 'max:2000'],
        ]);

        $invoice->sent = true;
        $invoice->save();

        $invoice->send(
            email: $request->input('email'),
            message: $request->input('message'),
        );

        return back();
    }
}
