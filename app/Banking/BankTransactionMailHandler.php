<?php

namespace App\Banking;

use App\Enums\BankTransactionAccountType;
use App\Models\BankTransactionAccount;
use App\Services\BankingService;
use BeyondCode\Mailbox\InboundEmail;
use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class BankTransactionMailHandler
{
    /**
     * List of registered parsers.
     */
    protected array $parsers = [];

    public function __construct(
        protected BankingService $bankingService,
    ) {}

    /**
     * Register a mail transaction parser for given account type.
     */
    public function registerParser(BankTransactionAccountType $accountType, MailParser|string|Closure $parser): static
    {
        $this->parsers[$accountType->value] = $parser;

        return $this;
    }

    /**
     * Resolve transaction parsers for given account type.
     */
    protected function resolveParser(BankTransactionAccountType $accountType): MailParser
    {
        $parser = Arr::get($this->parsers, $accountType->value);

        if ($parser instanceof MailParser) {
            return $parser;
        } elseif ($parser instanceof Closure) {
            return call_user_func($parser);
        } elseif (is_string($parser)) {
            return app($parser);
        }

        throw new InvalidArgumentException("Mail parser for [{$accountType->value}] has not been registered");
    }

    /**
     * Handle the incoming transaction email.
     */
    public function handle(BankTransactionAccount $account, InboundEmail $email): void
    {
        $parser = $this->resolveParser($account->type);

        if ($transaction = $parser->parse($email)) {
            DB::transaction(function () use ($account, $transaction) {
                $bankTransaction = $this->bankingService->recordTransaction($account, $transaction, ignoreDuplicate: false);

                $this->bankingService->pairTransaction($bankTransaction);
            });
        }
    }
}
