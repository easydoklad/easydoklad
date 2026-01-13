<?php

namespace App\Analytics;

use Brick\Money\Money;

class MoneyMetric extends Metric
{
    public function __construct(
        string $title,
        ?string $description,
        Money $value,
        ?Money $previousValue,
        ?Link $link,
        string $moneyFormattingLocale = 'sk',
    ) {
        parent::__construct($title, $description, $value, $previousValue, $link);

        $this->formatUsing(fn (Money $value) => $value->formatTo($moneyFormattingLocale));
    }

    /**
     * @param  Money  $value
     */
    public function resolveNumericValue(mixed $value): int|float
    {
        return $value->getMinorAmount()->toInt();
    }

    /**
     * @param  Money  $value
     * @param  Money  $previousValue
     */
    public function calculateChange(mixed $value, mixed $previousValue): ?Change
    {
        if ($previousValue->isZero()) {
            return null;
        }

        if ($value->isZero()) {
            return new Change($value, 0);
        }

        $change = $value->minus($previousValue);

        $current = $value->getMinorAmount()->toInt();
        $previous = $previousValue->getMinorAmount()->toInt();

        // Moving from loss to profit, or from profit to loss
        if (($previousValue->isNegative() && $value->isPositive()) || ($previousValue->isPositive() && $value->isNegative())) {
            return new Change(
                value: $change,
                percentage: ($current - $previous) / abs($previous) * 100
            );
        }

        // Both values are negative
        if ($value->isNegative() && $previousValue->isNegative()) {
            return new Change(
                value: $change,
                percentage: (abs($previous) - abs($current)) / abs($previous) * 100
            );
        }

        return new Change(
            value: $change,
            percentage: ($current - $previous) / $previous * 100
        );
    }

    /**
     * @param  Money  $value
     */
    public function isInversed(mixed $value): bool
    {
        if ($value->isNegative()) {
            return ! $this->inversed;
        }

        return $this->inversed;
    }

    /**
     * Create new metric instance.
     */
    public static function make(string $title, ComparableMoneyValue|Money $value, ?string $description = null, ?Link $link = null): static
    {
        return new static(
            title: $title,
            description: $description,
            value: $value instanceof ComparableMoneyValue ? $value->current : $value,
            previousValue: $value instanceof ComparableMoneyValue ? $value->previous : null,
            link: $link
        );
    }
}
