<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\PaymentMethod;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * @property \Brick\Money\Money $amount
 * @property Model $payable
 * @property \App\Models\User $recordedBy
 * @property PaymentMethod $method
 * @property \Carbon\Carbon $received_at
 * @property \App\Models\Account $account
 * @property \App\Models\BankTransaction $bankTransaction
 */
class Payment extends Model
{
    use HasUuid;

    protected $guarded = false;

    protected $casts = [
        'amount' => AsMoney::class,
        'method' => PaymentMethod::class,
        'received_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::deleted(function (Payment $payment) {
            if ($payment->payable instanceof Invoice) {
                $payment->payable->calculateTotals();
            }
        });
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function payable(): MorphTo
    {
        return $this->morphTo();
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function bankTransaction(): BelongsTo
    {
        return $this->belongsTo(BankTransaction::class);
    }
}
