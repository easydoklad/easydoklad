<?php


namespace App\Webhooks;


abstract class WebhookEvent
{
    /**
     * Define a new Webhook event.
     */
    public abstract static function define(): WebhookDefinition;
}
