<?php

namespace App\Webhooks;

abstract class WebhookEvent
{
    /**
     * Define a new Webhook event.
     */
    abstract public static function define(): WebhookDefinition;

    /**
     * Get an event payload.
     */
    abstract public function payload(): array;
}
