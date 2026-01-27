<?php

namespace App\Models;

use App\Enums\UserAccountRole;
use App\Mail\InvitationMail;
use App\Models\Concerns\HasUuid;
use Illuminate\Contracts\Cache\Lock;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Mail;

/**
 * @property string $email
 * @property UserAccountRole $role
 * @property \App\Models\Account $account
 * @property \Carbon\Carbon $expires_at
 * @property \Carbon\Carbon|null $accepted_at
 * @property \App\Models\User $invitedBy
 * @property \App\Models\User|null $acceptedBy
 */
class UserInvitation extends Model
{
    use HasUuid;

    protected $guarded = false;

    protected $casts = [
        'expires_at' => 'datetime',
        'accepted_at' => 'datetime',
        'role' => UserAccountRole::class,
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function acceptedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Send an invitation to the user.
     */
    public function send(): void
    {
        Mail::to($this->email)->send(new InvitationMail($this));
    }

    /**
     * Determine whether the invitation is expired.
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Determine whether the invitation has been accepted.
     */
    public function isAccepted(): bool
    {
        return $this->accepted_at !== null;
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

    /**
     * Accept the invitation by given user.
     */
    public function accept(User $user): void
    {
        $user->accounts()->attach($this->account, [
            'role' => $this->role,
        ]);

        $this->acceptedBy()->associate($user);
        $this->accepted_at = now();

        $this->save();
    }

    /**
     * Create atomic lock for this invitation.
     */
    public function lock(): Lock
    {
        return Cache::lock("Invitation:{$this->uuid}");
    }
}
