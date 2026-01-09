<?php

namespace App\Policies;

use App\Models\BankTransactionAccount;
use App\Models\User;

class BankTransactionAccountPolicy
{
    public function delete(User $user, BankTransactionAccount $bankTransactionAccount): bool
    {
        return $user->accounts->contains($bankTransactionAccount->account);
    }
}
