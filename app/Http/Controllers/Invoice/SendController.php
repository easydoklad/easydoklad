<?php

namespace App\Http\Controllers\Invoice;

use App\Models\Invoice;
use App\Support\Locale;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use StackTrace\Ui\Facades\Toast;
use Throwable;

class SendController
{
    public function __invoke(Request $request, Invoice $invoice)
    {
        Gate::authorize('view', $invoice);

        abort_if($invoice->draft, 400, 'Draft invoices cannot be sent');

        $request->validate([
            'email' => ['required', 'string', 'max:191', 'email'],
            'locale' => ['required', 'string', 'size:2', Rule::in(Locale::codes())],
            'message' => ['sometimes', 'required', 'string', 'max:2000'],
        ]);

        try {
            $email = $request->input('email');

            DB::transaction(fn () => $invoice->send(
                email: $email,
                message: $request->input('message'),
                locale: $request->input('locale'),
            ));

            Toast::make('Faktúra bola odoslaná.', $email, variant: 'positive');
        } catch (Throwable $e) {
            report($e);

            throw ValidationException::withMessages([
                'email' => 'Správu sa nepodarilo odoslať. Skontrolujte nastavenia odosielania emailov.',
            ]);
        }

        return back();
    }
}
