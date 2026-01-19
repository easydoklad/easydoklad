<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property \App\Models\Account $account
 * @property string $name
 * @property string $url
 * @property bool $active
 * @property string $secret
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\WebhookEvent> $events
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\DispatchedWebhook> $dispatchedWebhooks
 */
class Webhook extends Model
{
    use HasUuid;

    protected $guarded = false;

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::deleting(function (Webhook $webhook) {
            $webhook->events()->delete();
            $webhook->dispatchedWebhooks()->delete();
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function events(): HasMany
    {
        return $this->hasMany(WebhookEvent::class);
    }

    public function dispatchedWebhooks(): HasMany
    {
        return $this->hasMany(DispatchedWebhook::class);
    }
}
