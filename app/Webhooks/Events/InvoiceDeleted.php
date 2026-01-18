<?php


namespace App\Webhooks\Events;


use App\Enums\WebhookGroup;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookEvent;

class InvoiceDeleted extends WebhookEvent
{
    public static function define(): WebhookDefinition
    {
        return new WebhookDefinition(
            id: 'invoice.deleted',
            description: 'Udalosť je vyvolaná keď je existujúca faktúra odstránená.',
            group: WebhookGroup::Invoices,
        );
    }
}
