<?php

namespace App\Banking;

use App\Enums\BankTransactionSource;
use App\Enums\BankTransactionType;
use Brick\Money\Money;
use Carbon\Carbon;

final readonly class PendingTransaction
{
    public function __construct(
        public BankTransactionSource $source,
        public BankTransactionType $type,
        public Carbon $date,
        public ?string $sentFromName,
        public string $sentFromIban,
        public string $receivedToIban,
        public Money $amount,
        public ?string $variableSymbol,
        public ?string $specificSymbol,
        public ?string $constantSymbol,
        public ?string $description,
        public ?string $reference,
    ) {}

    public function hash(): string
    {
        $payload = json_encode([
            $this->source->value,
            $this->type->value,
            $this->date->format('Ymd'),
            $this->sentFromName,
            $this->sentFromIban,
            $this->receivedToIban,
            $this->amount->getMinorAmount()->toInt(),
            $this->amount->getCurrency()->getCurrencyCode(),
            $this->variableSymbol,
            $this->specificSymbol,
            $this->constantSymbol,
            $this->description,
            $this->reference,
        ]);

        return hash('sha256', $payload);
    }
}
