<?php


namespace App\Webhooks\Events;


use App\Enums\WebhookGroup;
use App\Webhooks\WebhookDefinition;
use App\Webhooks\WebhookEvent;

class InvoicePaid extends WebhookEvent
{
    public static function define(): WebhookDefinition
    {
        return new WebhookDefinition(
            id: 'invoice.paid',
            description: 'Udalosť je vyvolaná v prípade ak faktúra bola úplne uhradená.',
            group: WebhookGroup::Invoices,
        );
    }
}
