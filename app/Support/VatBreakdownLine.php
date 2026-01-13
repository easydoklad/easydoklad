<?php

namespace App\Support;

use Brick\Math\BigNumber;
use Brick\Money\Money;

readonly class VatBreakdownLine
{
    public function __construct(
        public BigNumber $rate,
        public Money $total,
        public Money $base,
    ) {}
}
