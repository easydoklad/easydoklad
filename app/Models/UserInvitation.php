<?php

namespace App\Models;

use App\Enums\UserAccountRole;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property string $email
 * @property UserAccountRole $role
 * @property \Carbon\Carbon $expires_at
 */
class UserInvitation extends Model
{
    use HasUuid;

    protected $guarded = false;

    protected $casts = [
        'expires_at' => 'datetime',
        'role' => UserAccountRole::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Send an invitation to the user.
     */
    public function send(): void
    {
        // TODO: WIP
    }

    /**
     * Determine whether the invitation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Prolong the invitation.
     */
    public function prolong(): static
    {
        $this->expires_at = now()->addHours(config('app.invitation_expiration_hours'));

        $this->save();

        return $this;
    }
}
