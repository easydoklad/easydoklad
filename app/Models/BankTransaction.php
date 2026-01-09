<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Enums\BankTransactionType;
use App\Models\Concerns\HasUuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property \App\Models\Account $account
 * @property \App\Models\BankTransactionAccount $bankTransactionAccount
 * @property BankTransactionType $type
 * @property string $sent_from_iban
 * @property string|null $sent_from_name
 * @property string $received_to_iban
 * @property \Brick\Money\Money $amount
 * @property \Carbon\Carbon $transaction_date
 * @property string|null $variable_symbol
 * @property string|null $specific_symbol
 * @property string|null $constant_symbol
 * @property string|null $description
 * @property string|null $reference
 * @property string $hash
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Payment> $payments
 */
class BankTransaction extends Model
{
    use HasUuid, SoftDeletes;

    protected $guarded = false;

    protected $casts = [
        'type' => BankTransactionType::class,
        'amount' => AsMoney::class,
        'transaction_date' => 'date',
    ];

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function bankTransactionAccount(): BelongsTo
    {
        return $this->belongsTo(BankTransactionAccount::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }
}
