<?php

namespace App\Support;

use Brick\Math\BigNumber;
use Brick\Math\RoundingMode;
use Brick\Money\Exception\MoneyMismatchException;
use Brick\Money\Money;
use InvalidArgumentException;
use RuntimeException;

final readonly class MoneyUtils
{
    /**
     * Calculate total sum of money.
     */
    public static function sum(string $currency, Money ...$monies): Money
    {
        try {
            return Money::total(Money::zero($currency), ...$monies);
        } catch (MoneyMismatchException $e) {
            throw new RuntimeException($e->getMessage(), previous: $e);
        }
    }

    /**
     * Adds given amount of percent to the money.
     *
     * @param  \Brick\Money\Money  $base  The amount considered as 100%
     * @param  \Brick\Math\BigNumber|int|float  $rate  How much % of the base should be added
     * @return array{Money, Money} tupple: base + added money, only added money
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public static function addPercent(Money $base, BigNumber|int|float $rate, ?RoundingMode $roundingMode = RoundingMode::HALF_UP): array
    {
        $rate = BigNumber::of($rate);

        if ($rate->isNegative() || $rate->isGreaterThan(100)) {
            throw new InvalidArgumentException('The rate muse be a number between 0 and 100 (inclsive).');
        }

        if ($base->isZero()) {
            return [$base, Money::zero($base->getCurrency())];
        }

        $addition = $base
            ->toRational()
            ->dividedBy(100)
            ->multipliedBy($rate)
            ->to($base->getContext(), $roundingMode);

        return [$base->plus($addition), $addition];
    }

    /**
     * Subtracts given amount of percent from the money.
     *
     * @param  \Brick\Money\Money  $base  The amount considered as 100%
     * @param  \Brick\Math\BigNumber|int|float  $rate  How much % of the base should be subtracted
     * @return array{Money, Money} tupple: base + subtracted money, only subtracted money
     *
     * @throws \Brick\Money\Exception\MoneyMismatchException
     */
    public static function subPercent(Money $base, BigNumber|int|float $rate, ?RoundingMode $roundingMode = RoundingMode::HALF_UP): array
    {
        $rate = BigNumber::of($rate);

        if ($rate->isNegative() || $rate->isGreaterThan(100)) {
            throw new InvalidArgumentException('The rate muse be a number between 0 and 100 (inclsive).');
        }

        if ($base->isZero()) {
            return [$base, Money::zero($base->getCurrency())];
        }

        $subtraction = $base
            ->toRational()
            ->dividedBy(BigNumber::of(100)->plus($rate))
            ->multipliedBy(100)
            ->to($base->getContext(), $roundingMode);

        return [$base->minus($subtraction), $subtraction];
    }
}
