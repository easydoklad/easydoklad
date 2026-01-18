<?php


namespace App\Webhooks\Events;


use App\Enums\WebhookGroup;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookEvent;

class InvoiceUpdated extends WebhookEvent
{
    public static function define(): WebhookDefinition
    {
        return new WebhookDefinition(
            id: 'invoice.updated',
            description: 'Udalosť je vyvolaná keď je upravená existujúca faktúra.',
            group: WebhookGroup::Invoices,
        );
    }
}
