<?php

namespace App\Http\Controllers;

use App\Facades\Accounts;
use App\Models\Account;
use Illuminate\Support\Facades\Gate;

class SwitchAccountController
{
    public function __invoke(Account $account)
    {
        Gate::authorize('view', $account);

        Accounts::switch($account);

        return back();
    }
}
