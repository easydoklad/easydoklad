<?php


namespace App\Services;


use App\Banking\PendingTransaction;
use App\Enums\PaymentMethod;
use App\Models\BankTransaction;
use App\Models\BankTransactionAccount;
use App\Models\Invoice;
use Illuminate\Support\Facades\Facade;

class BankingService extends Facade
{
    /**
     * Record a new bank transaction.
     */
    public function recordTransaction(BankTransactionAccount $account, PendingTransaction $transaction, bool $ignoreDuplicate = true): BankTransaction
    {
        $userAccount = $account->account;

        $existingTransaction = BankTransaction::query()->firstWhere('hash', $transaction->hash());

        if ($existingTransaction && $ignoreDuplicate) {
            return $existingTransaction;
        }

        $bankTransaction = new BankTransaction([
            'type' => $transaction->type,
            'sent_from_iban' => $transaction->sentFromIban,
            'sent_from_name' => $transaction->sentFromName,
            'received_to_iban' => $transaction->receivedToIban,
            'amount' => $transaction->amount,
            'transaction_date' => $transaction->date,
            'variable_symbol' => $transaction->variableSymbol,
            'specific_symbol' => $transaction->specificSymbol,
            'constant_symbol' => $transaction->constantSymbol,
            'description' => $transaction->description,
            'reference' => $transaction->reference,
            'hash' => $transaction->hash(),
        ]);
        $bankTransaction->account()->associate($userAccount);
        $bankTransaction->bankTransactionAccount()->associate($account);
        $bankTransaction->save();

        return $bankTransaction;
    }

    /**
     * Pair given bank transaction with a payable.
     */
    public function pairTransaction(BankTransaction $transaction): void
    {
        if ($transaction->payments->isNotEmpty()) {
            return;
        }

        if (!$transaction->variable_symbol) {
            return;
        }

        /** @var Invoice $invoice */
        $invoice = Invoice::query()
            ->where('variable_symbol', $transaction->variable_symbol)
            ->whereBelongsTo($transaction->account)
            ->first();

        if (! $invoice) {
            return;
        }

        /** @var \App\Models\Payment $existingPayment */
        if (
            $existingPayment = $invoice->payments()
                ->where('method', PaymentMethod::BankTransfer)
                ->whereNull('bank_transaction_id')
                ->where('amount', $transaction->amount->getMinorAmount()->toInt())
                ->where('currency', $transaction->amount->getCurrency()->getCurrencyCode())
                ->first()
        ) {
            $existingPayment->bankTransaction()->associate($transaction)->save();
        } else {
            $payment = $invoice->addPayment(
                amount: $transaction->amount,
                method: PaymentMethod::BankTransfer,
                receivedAt: $transaction->transaction_date,
            );

            $payment->bankTransaction()->associate($transaction)->save();
        }
    }
}
