<?php

namespace App\Analytics;

use Brick\Money\Money;

final readonly class ChartValue
{
    public function __construct(
        public string $label,
        public Money|int|float $value,
    ) {}

    public function getNumericValue(): int|float
    {
        if ($this->value instanceof Money) {
            return $this->value->getMinorAmount()->toInt();
        }

        return $this->value;
    }
}
