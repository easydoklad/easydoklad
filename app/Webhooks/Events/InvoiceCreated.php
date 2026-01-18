<?php


namespace App\Webhooks\Events;


use App\Enums\WebhookGroup;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookEvent;

class InvoiceCreated extends WebhookEvent
{
    public static function define(): WebhookDefinition
    {
        return new WebhookDefinition(
            id: 'invoice.created',
            description: 'Udalosť je vyvolaná keď je vytvorená nová faktúra.',
            group: WebhookGroup::Invoices,
        );
    }
}
