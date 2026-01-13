<?php

namespace App\Http\Controllers\Banking;

use App\Facades\Accounts;
use App\Models\BankTransaction;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use StackTrace\Ui\DateRange;
use StackTrace\Ui\NumberValue;
use StackTrace\Ui\Table;
use StackTrace\Ui\Table\Actions;
use StackTrace\Ui\Table\Columns;
use StackTrace\Ui\Table\Filters;

class BankTransactionController
{
    public function index()
    {
        $account = Accounts::current();

        $bankAccounts = $account->bankTransactionAccounts()->count();

        $transactions = Table::make($account->bankTransactions()->getQuery())
            ->searchable(function (Builder $builder, string $term) {
                $builder->where(function (Builder $query) use ($term) {
                    $query
                        ->where('sent_from_name', 'like', '%'.$term.'%')
                        ->orWhere('sent_from_iban', 'like', '%'.$term.'%');
                });
            })
            ->withColumns([
                Columns\Icon::make('', fn () => 'arrow-up-right')
                    ->style(function (Table\Style $style) {
                        $style->color('positive');
                    })
                    ->width(10),

                Columns\Text::make('Obchodník', fn (BankTransaction $transaction) => $transaction->sent_from_name)
                    ->fontMedium(),

                Columns\Text::make('Z účtu', fn (BankTransaction $transaction) => $transaction->sent_from_iban)
                    ->width(56)
                    ->numsTabular(),

                Columns\Text::make('Na účet', fn (BankTransaction $transaction) => $transaction->received_to_iban)
                    ->width(56)
                    ->numsTabular(),

                Columns\Text::make('Suma', fn (BankTransaction $transaction) => $transaction->amount->formatTo($account->getMoneyFormattingLocale()))
                    ->alignRight()
                    ->numsTabular()
                    ->sortable(using: 'amount', named: 'amount'),

                Columns\Date::make('Dátum', 'transaction_date')
                    ->sortable(using: 'transaction_date', default: Table\Direction::Desc, named: 'date')
                    ->width(24),
            ])
            ->withFilters([
                Filters\DateRange::make('Dátum', 'date')
                    ->using(fn (Builder $builder, DateRange $range) => $range->applyToQuery($builder, 'transaction_date')),

                Filters\Number::make('Suma', 'amount')
                    ->using(function (Builder $builder, NumberValue $value) {
                        $moneyValue = new NumberValue(
                            operator: $value->operator,
                            value1: is_numeric($value->value1) ? (int) round($value->value1 * 100) : null,
                            value2: is_numeric($value->value2) ? (int) round($value->value2 * 100) : null,
                        );

                        $moneyValue->where($builder, 'amount');
                    }),
            ])
            ->withActions([
                Actions\Event::make('', 'show')
                    ->icon('eye')
                    ->inline(),
            ])
            ->mapResource(fn (BankTransaction $transaction) => [
                'id' => $transaction->uuid,
                'date' => $transaction->transaction_date->format('d.m.Y'),
                'sentFromIban' => $transaction->sent_from_iban,
                'sentFromName' => $transaction->sent_from_name,
                'receivedToIban' => $transaction->received_to_iban,
                'reference' => $transaction->reference,
                'description' => $transaction->description,
                'variableSymbol' => $transaction->variable_symbol,
                'constantSymbol' => $transaction->constant_symbol,
                'specificSymbol' => $transaction->specific_symbol,
                'amount' => $transaction->amount->formatTo($account->getMoneyFormattingLocale()),
            ]);

        return Inertia::render('Banking/BankTransactionList', [
            'hasConnectedBankAccounts' => $bankAccounts > 0,
            'transactions' => $transactions,
        ]);
    }
}
