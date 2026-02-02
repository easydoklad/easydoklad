<?php

namespace App\Http\Controllers\API;

use App\Facades\Accounts;
use App\Models\Invoice;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Throwable;

class SendInvoiceController extends Controller
{
    public function __invoke(Request $request, Invoice $invoice)
    {
        abort_unless(Accounts::current()->is($invoice->account), 403);
        abort_if($invoice->draft, 400, 'Draft invoices cannot be sent');

        $request->validate([
            'email' => ['required', 'string', 'max:191', 'email'],
            'locale' => ['nullable', 'string', 'size:2', Rule::in(Locale::codes())],
            'message' => ['nullable', 'string', 'max:2000'],
        ]);

        try {
            DB::transaction(fn () => $invoice->send(
                email: $request->input('email'),
                message: $request->input('message'),
                locale: $request->input('locale'),
            ));
        } catch (Throwable $e) {
            report($e);

            abort(400, 'Správu sa nepodarilo odoslať. Skontrolujte nastavenia odosielania emailov.');
        }

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
