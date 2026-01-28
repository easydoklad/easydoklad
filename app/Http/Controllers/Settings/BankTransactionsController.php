<?php

namespace App\Http\Controllers\Settings;

use App\Facades\Accounts;
use App\Models\BankTransactionAccount;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;

class BankTransactionsController
{
    public function __invoke()
    {
        $account = Accounts::current();

        Gate::authorize('update', $account);

        $bankAccounts = $account->bankTransactionAccounts;

        return Inertia::render('Settings/BankTransactions', [
            'bankAccounts' => $bankAccounts->map(fn (BankTransactionAccount $bankAccount) => [
                'id' => $bankAccount->uuid,
                'name' => $bankAccount->name,
                'iban' => $bankAccount->iban,
                'bankAccountType' => [
                    'label' => $bankAccount->type->label(),
                    'value' => $bankAccount->type->value,
                ],
                'mailIntegration' => $bankAccount->type->worksThroughMailNotifications() ? [
                    'email' => $bankAccount->getInboundMail(),
                    'helpLink' => $bankAccount->type->getConfigurationHelpLink(),
                ] : null,
            ]),
        ]);
    }
}
