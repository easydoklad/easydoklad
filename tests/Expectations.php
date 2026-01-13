<?php

use Brick\Money\Money;

expect()->extend('toBeMoney', function (int $price, string $currency = 'EUR') {
    if ($this->value instanceof Money) {
        $value = $this->value;
    } else {
        $value = null;
    }

    expect($value)
        ->toBeInstanceOf(Money::class)
        ->and($value->getMinorAmount()->toInt())
        ->toBe($price)
        ->and($value->getCurrency()->getCurrencyCode())
        ->toBe($currency);
});
