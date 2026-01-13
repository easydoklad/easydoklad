<?php

namespace App\Services;

use App\Models\Account;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Session\Session;
use InvalidArgumentException;

class AccountService
{
    public function __construct(
        protected Session $session,
        protected Guard $auth,
    ) {}

    /**
     * Get the current user account.
     */
    public function current(): Account
    {
        /** @var \App\Models\User|\App\Models\Account $user */
        $user = $this->auth->user();

        if ($user instanceof Account) {
            return $user;
        }

        if (! $user) {
            throw new InvalidArgumentException('The user is not authenticated');
        }

        if ($id = $this->session->get('account')) {
            if ($account = $user->accounts->firstWhere('id', $id)) {
                return $account;
            }
        }

        if ($lastAccount = $user->last_account_id) {
            if ($account = $user->accounts->firstWhere('id', $lastAccount)) {
                return $account;
            }
        }

        return $user->accounts->first();
    }

    /**
     * Switch the user account.
     */
    public function switch(Account $account): void
    {
        /** @var \App\Models\User $user */
        $user = $this->auth->user();

        if (! $user) {
            throw new InvalidArgumentException('The user is not authenticated');
        }

        if ($user->accounts->where('id', $account->id)->isNotEmpty()) {
            $this->session->put('account', $account->id);
            $this->session->save();

            $user->last_account_id = $account->id;
            $user->save();
        } else {
            throw new InvalidArgumentException('The user does not have permission to access this account');
        }
    }
}
