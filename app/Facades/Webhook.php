<?php


namespace App\Facades;


use App\Webhooks\WebhookManager;
use Illuminate\Support\Facades\Facade;

/**
 * @method static void dispatch(\App\Models\Account $account, \App\Webhooks\WebhookEvent $event)
 */
class Webhook extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WebhookManager::class;
    }
}
