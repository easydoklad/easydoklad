<?php

namespace App\Analytics;

use Brick\Money\Currency;
use Brick\Money\Money;

final readonly class ComparableMoneyValue
{
    public function __construct(
        public Money $current,
        public Money $previous
    ) {}

    /**
     * Create zero value.
     */
    public static function zero(Currency|string $currency): ComparableMoneyValue
    {
        $currency = $currency instanceof Currency ? $currency : Currency::of($currency);

        return new ComparableMoneyValue(Money::zero($currency), Money::zero($currency));
    }
}
