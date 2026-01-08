<?php

namespace App\Models;

use App\Enums\BankTransactionAccountType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
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
    use HasUuid;

    protected $guarded = false;

    protected $casts = [
        'meta' => 'json',
        'type' => BankTransactionAccountType::class,
    ];

    protected static function booted(): void
    {
        static::deleting(function (BankTransactionAccount $account) {
            // TODO: Odstranit transakcie
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    /**
     * Get the mail address for receiving transaction emails.
     */
    public function getInboundMail(): string
    {
        return "banka+{$this->uuid}@".config('app.mailbox_domain');
    }
}
