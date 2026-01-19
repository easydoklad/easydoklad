<?php

namespace App\Models;

use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Http\Client\Response;
use Throwable;

/**
 * @property string $event
 * @property \App\Models\Webhook $webhook
 * @property int $attempts
 * @property array $payload
 * @property array|null $failures
 * @property \Carbon\Carbon|null $last_attempt_at
 * @property \Carbon\Carbon|null $delivered_at
 */
class DispatchedWebhook extends Model
{
    use HasUuid;

    protected $guarded = false;

    protected $casts = [
        'last_attempt_at' => 'datetime',
        'delivered_at' => 'datetime',
        'payload' => 'array',
        'failures' => 'array',
    ];

    public function webhook(): BelongsTo
    {
        return $this->belongsTo(Webhook::class);
    }

    /**
     * Determine whether a webhook has been delivered.
     */
    public function delivered(): bool
    {
        return $this->delivered_at != null;
    }

    /**
     * Mark webhook as delivered.
     */
    public function markDelivered(): static
    {
        $this->delivered_at = now();

        return $this;
    }

    /**
     * Increment attempt count.
     */
    public function incrementAttempts(): static
    {
        $this->attempts = $this->attempts + 1;
        $this->last_attempt_at = now();

        return $this;
    }

    /**
     * Add a failure record.
     */
    public function addFailure(Throwable|Response $failure): static
    {
        if ($failure instanceof Response) {
            $fail = [
                'time' => now()->toIso8601ZuluString(),
                'exception' => null,
                'response' => [
                    'body' => $failure->body(),
                    'headers' => $failure->headers(),
                    'status' => $failure->status(),
                ],
            ];
        } else {
            $fail = [
                'time' => now()->toIso8601ZuluString(),
                'exception' => [
                    'message' => $failure->getMessage(),
                    'trace' => $failure->getTraceAsString(),
                ],
                'response' => null,
            ];
        }

        $failures = $this->failures ?: [];
        $failures[] = $fail;
        $this->failures = $failures;

        return $this;
    }
}
