<?php

namespace App\Facades;

use App\Models\Account;
use Illuminate\Support\Facades\Facade;

/**
 * @method static Account current()
 * @method static void switch(Account $account)
 */
class Accounts extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'accounts';
    }
}
