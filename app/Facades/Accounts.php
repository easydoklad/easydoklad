<?php

namespace App\Facades;

use App\Models\Account;
use Illuminate\Support\Facades\Facade;

/**
 * @method static bool check()
 * @method static Account current()
 * @method static Account|null get()
 * @method static void switch(Account $account)
 */
class Accounts extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'accounts';
    }
}
