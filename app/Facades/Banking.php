<?php


namespace App\Facades;


use App\Banking\PendingTransaction;
use App\Models\BankTransaction;
use App\Models\BankTransactionAccount;
use Illuminate\Support\Facades\Facade;

/**
 * @method static BankTransaction recordTransaction(BankTransactionAccount $account, PendingTransaction $transaction, bool $ignoreDuplicate = true)
 */
class Banking extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'banking';
    }
}
