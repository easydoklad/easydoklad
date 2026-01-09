<?php

namespace App\Models;

use App\Enums\BankTransactionAccountType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\Account $account
 * @property string $name
 * @property string $iban
 * @property BankTransactionAccountType $type
 * @property array|null $meta
 */
class BankTransactionAccount extends Model
{
    use HasUuid, SoftDeletes;

    protected $guarded = false;

    protected $casts = [
        'meta' => 'json',
        'type' => BankTransactionAccountType::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (BankTransactionAccount $account) {
            $account->transactions()->delete();
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    /**
     * Get the mail address for receiving transaction emails.
     */
    public function getInboundMail(): string
    {
        return "banka+{$this->uuid}@".config('app.mailbox_domain');
    }
}
