<?php

namespace App\Http\Controllers\Invoice;

use App\Models\Invoice;
use App\Templating\InvoiceSerializer;
use App\Templating\SerializerOptions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DownloadInvoiceController
{
    public function __invoke(Request $request, Invoice $invoice, InvoiceSerializer $serializer)
    {
        Gate::authorize('view', $invoice);

        abort_if($invoice->draft, 400, 'Draft invoices cannot be downloaded');

        $request->validate([
            'locale' => ['nullable', 'string', 'min:2', 'max:2'],
        ]);

        $template = $invoice->template;

        $locale = $template->resolveLocale($request->input('locale') ?: $invoice->account->getPreferredDocumentLocale());

        $input = $serializer->serialize(
            invoice: $invoice,
            options: new SerializerOptions(
                locale: $locale,
                moneyFormattingLocale: $invoice->account->getMoneyFormattingLocale(),
            )
        );

        return response()->streamDownload(function () use ($template, $input) {
            echo $template->render($input);
        }, $invoice->createFileName($locale, extension: 'pdf'));
    }
}
