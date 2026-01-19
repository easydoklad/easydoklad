<?php

namespace App\Listeners;

use App\Events\InvoicePaid;
use App\Facades\Webhook;

class InvoicePaidListener
{
    public function handle(InvoicePaid $event): void
    {
        $invoice = $event->invoice;

        Webhook::dispatch($invoice->account, new \App\Webhooks\Events\InvoicePaid($invoice));
    }
}
