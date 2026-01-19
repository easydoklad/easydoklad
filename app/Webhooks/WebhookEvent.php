<?php


namespace App\Webhooks;


abstract class WebhookEvent
{
    /**
     * Define a new Webhook event.
     */
    public abstract static function define(): WebhookDefinition;

    /**
     * Get an event payload.
     */
    public abstract function payload(): array;
}
