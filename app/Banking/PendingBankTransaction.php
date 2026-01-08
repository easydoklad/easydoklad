<?php


namespace App\Banking;


use App\Enums\BankTransactionSource;
use Brick\Money\Money;
use Carbon\Carbon;

final readonly class PendingBankTransaction
{
    public function __construct(
        public BankTransactionSource $source,
        public ?Carbon               $date,
        public ?string               $sentFromName,
        public string                $sentFromIban,
        public string                $receivedToIban,
        public Money                 $amount,
        public ?string               $variableSymbol,
        public ?string               $specificSymol,
        public ?string               $constantSymbol,
        public ?string               $description,
        public ?string               $reference,
    ) { }
}
