<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property \App\Models\Webhook $webhook
 * @property string $event
 */
class WebhookEvent extends Model
{
    public $timestamps = false;

    protected $guarded = false;

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }
}
