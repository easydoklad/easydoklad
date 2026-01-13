<?php

namespace App\Analytics;

use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Number;

class Metric implements Arrayable
{
    /**
     * Determine whether a tooltip should be displayed over change.
     */
    protected bool $showChangeTooltip = true;

    /**
     * Custom callback for formatting metric value.
     */
    protected ?Closure $formatUsing = null;

    /**
     * Determine whether metric whether positive-up and negative-down
     * trend should be inversed to positive-down and negatuve-up.
     */
    protected bool $inversed = false;

    /**
     * Custom trend style resolver.
     */
    protected ?Closure $trendStyleResolver = null;

    public function __construct(
        protected string $title,
        protected ?string $description,
        protected mixed $value,
        protected mixed $previousValue,
        protected ?Link $link,
    ) {}

    /**
     * Show change tooltip containing value of the change.
     */
    public function showChangeTooltip(bool $show = true): static
    {
        $this->showChangeTooltip = $show;

        return $this;
    }

    /**
     * Hide change tooltip containing value of the change.
     */
    public function hideChangeTooltip(bool $hide = true): static
    {
        return $this->showChangeTooltip(! $hide);
    }

    /**
     * Retrieve link to the metric.
     */
    public function link(): ?Link
    {
        return $this->link;
    }

    /**
     * Set link to the metric.
     */
    public function withLink(?Link $link): static
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Get the metric title.
     */
    public function title(): string
    {
        return $this->title;
    }

    /**
     * Set the metric title.
     */
    public function withTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    /**
     * Get the metric description.
     */
    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * Set the metric description.
     */
    public function withDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Calculate value of the metric.
     */
    public function resolveValue(): mixed
    {
        return $this->value;
    }

    /**
     * Calculate previous value of the metric.
     */
    public function resolvePreviousValue(): mixed
    {
        return $this->previousValue;
    }

    /**
     * Resolve numeric value of the metric value.
     */
    public function resolveNumericValue(mixed $value): int|float
    {
        return $this->value;
    }

    /**
     * Set custom value formatter.
     */
    public function formatUsing(?Closure $closure): static
    {
        $this->formatUsing = $closure;

        return $this;
    }

    /**
     * Set whether metric is inversed.
     */
    public function inversed(bool $inversed = true): static
    {
        $this->inversed = $inversed;

        return $this;
    }

    /**
     * Get whether metric is inversed.
     */
    public function isInversed(mixed $value): bool
    {
        if ($value < 0) {
            return ! $this->inversed;
        }

        return $this->inversed;
    }

    /**
     * Get the calcualted metric value.
     */
    public function getValue(): mixed
    {
        return $this->resolveValue();
    }

    /**
     * Get previous metric value.
     */
    public function getPreviousValue(): mixed
    {
        return $this->resolvePreviousValue();
    }

    /**
     * Format metric value for display.
     */
    public function formatDisplayValue(mixed $value): ?string
    {
        if ($this->formatUsing instanceof Closure) {
            return call_user_func($this->formatUsing, $value);
        }

        return $value;
    }

    /**
     * Calculate change between two metric values.
     */
    public function calculateChange(mixed $value, mixed $previousValue): ?Change
    {
        if ($value == 0 && $previousValue == 0) {
            return null;
        }

        if ($previousValue != 0) {
            return new Change(
                value: $value - $previousValue,
                percentage: ($value - $previousValue) / $previousValue * 100
            );
        }

        if ($value == 0) {
            return new Change(0, 0);
        }

        return null;
    }

    /**
     * Formate change value.
     */
    public function formatChangeValue(Change $change): string
    {
        $formatted = Number::percentage($change->percentage, maxPrecision: 2);

        if ($change->percentage > 0) {
            return "+{$formatted}";
        }

        return $formatted;
    }

    /**
     * Resolve a trend style.
     */
    protected function resolveTrendStyle(Change $change): TrendStyle
    {
        if ($this->trendStyleResolver instanceof Closure) {
            return call_user_func($this->trendStyleResolver, $change);
        }

        if ($change->trend() === Trend::Increasing) {
            return $this->inversed ? TrendStyle::Negative : TrendStyle::Positive;
        } elseif ($change->trend() === Trend::Decreasing) {
            return $this->inversed ? TrendStyle::Positive : TrendStyle::Negative;
        }

        return TrendStyle::Neutral;
    }

    /**
     * Render metric to view.
     */
    public function toArray(): array
    {
        $value = $this->getValue();
        $previousValue = $this->getPreviousValue();

        if ($value !== null && $previousValue !== null) {
            $change = $this->calculateChange($value, $previousValue);
        } else {
            $change = null;
        }

        $link = $this->link();

        return [
            'title' => $this->title(),
            'description' => $this->description(),
            'value' => $this->formatDisplayValue($value),
            'numericValue' => $this->resolveNumericValue($value),
            'previousValue' => $previousValue !== null ? $this->formatDisplayValue($previousValue) : null,
            'change' => $change ? [
                'value' => $this->formatChangeValue($change),
                'percentage' => $change->percentage,
                'numericValue' => $this->resolveNumericValue($change->value),
                'difference' => $this->formatDisplayValue($change->value),
                'trend' => $change->trend()->value,
                'trendStyle' => $this->resolveTrendStyle($change)->value,
                'isInversed' => $this->isInversed($value),
                'showTooltip' => $this->showChangeTooltip,
            ] : null,
            'link' => $link ? [
                'label' => $link->label,
                'url' => $link->url,
                'isExternal' => $link->isExternal,
            ] : null,
        ];
    }
}
