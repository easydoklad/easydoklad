<?php

namespace App\Webhooks\Events;

use App\Enums\WebhookGroup;
use App\Models\Invoice;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookEvent;

class InvoiceUpdated extends WebhookEvent
{
    public function __construct(
        protected Invoice $invoice
    ) {}

    public function payload(): array
    {
        return [
            'invoice' => $this->invoice->toResource(),
        ];
    }

    public static function define(): WebhookDefinition
    {
        return new WebhookDefinition(
            id: 'invoice.updated',
            description: 'Udalosť je vyvolaná keď je upravená existujúca faktúra.',
            group: WebhookGroup::Invoices,
        );
    }
}
