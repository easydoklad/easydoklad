<?php

namespace App\Models;

use App\Enums\UserAccountRole;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Pennant\Concerns\HasFeatures;

/**
 * @property string $name
 * @property string $email
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Account> $accounts
 * @property int|null $last_account_id
 */
class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, HasFeatures, HasUuid, Notifiable;

    protected $guarded = false;

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function accounts(): BelongsToMany
    {
        return $this->belongsToMany(Account::class)->withPivot(['role']);
    }

    /**
     * Get a user role within account, if loaded.
     */
    public function getRole(): ?UserAccountRole
    {
        if ($this->pivot) {
            return UserAccountRole::from($this->pivot->role);
        }

        return null;
    }
}
