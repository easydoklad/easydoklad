<?php


namespace App\Webhooks\Events;


use App\Enums\WebhookGroup;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookEvent;

class InvoiceIssued extends WebhookEvent
{
    public static function define(): WebhookDefinition
    {
        return new WebhookDefinition(
            id: 'invoice.issued',
            description: 'Udalosť je vyvolaná keď je faktúra vystavená.',
            group: WebhookGroup::Invoices,
        );
    }
}
