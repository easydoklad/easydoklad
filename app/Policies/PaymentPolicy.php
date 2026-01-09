<?php

namespace App\Policies;

use App\Models\Payment;
use App\Models\User;

class PaymentPolicy
{
    public function delete(User $user, Payment $payment): bool
    {
        return $user->accounts->contains($payment->account);
    }
}
