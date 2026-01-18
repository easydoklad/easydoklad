<?php


namespace App\Facades;


use App\Webhooks\WebhookManager;
use Illuminate\Support\Facades\Facade;

class Webhook extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WebhookManager::class;
    }
}
