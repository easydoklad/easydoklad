<?php

namespace App\Analytics;

use Carbon\Carbon;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Money\Money;

class Chart implements Arrayable
{
    /**
     * List of chart values.
     */
    protected array $values = [];

    /**
     * Determine whether legend should be displayed.
     */
    protected bool $showLegend = true;

    /**
     * Value format for chart.
     */
    protected string $format = 'number';

    /**
     * Max precision when formatting as number.
     */
    protected int $maxPrecision = 0;

    /**
     * Set number format as money.
     */
    public function formatAsMoney(): static
    {
        $this->format = 'money';

        return $this;
    }

    /**
     * Set number format as number.
     */
    public function formatAsNumber(): static
    {
        $this->format = 'number';

        return $this;
    }

    /**
     * Set number format as percentage.
     */
    public function formatAsPercentage(): static
    {
        $this->format = 'percentage';

        return $this;
    }

    /**
     * Set maximum precision when formatting as number.
     */
    public function maxPrecision(int $precision): static
    {
        $this->maxPrecision = $precision;

        return $this;
    }

    /**
     * Show legend for this chart.
     */
    public function showLegend(bool $show = true): static
    {
        $this->showLegend = $show;

        return $this;
    }

    /**
     * Hide legend for this chart.
     */
    public function hideLegend(bool $hide = true): static
    {
        return $this->showLegend(! $hide);
    }

    /**
     * Add a value to the chart.
     */
    public function value(string $label, ChartValue ...$values): static
    {
        $this->values[] = ['label' => $label, 'values' => $values];

        if (Arr::first($values)?->value instanceof Money) {
            $this->formatAsMoney();
        }

        return $this;
    }

    /**
     * Add value to the chart for given month.
     */
    public function valueForMonth(Carbon $date, ChartValue ...$value): static
    {
        return $this->value(Str::ucfirst($date->isoFormat('MMMM Y')), ...$value);
    }

    /**
     * Retrieve values of this chart.
     */
    public function getValues(): Collection
    {
        return collect($this->values);
    }

    /**
     * Prepare chart for view.
     */
    public function toArray(): array
    {
        return [
            'points' => $this->getValues()->map(function (array $chartValue) {
                return [
                    'group' => $chartValue['label'],
                    'values' => collect($chartValue['values'])->map(fn (ChartValue $value) => [
                        'label' => $value->label,
                        'value' => $value->getNumericValue(),
                    ])->values(),
                ];
            }),
            'showLegend' => $this->showLegend,
            'format' => $this->format,
            'maxPrecision' => $this->maxPrecision,
        ];
    }

    /**
     * Create new chart instance.
     */
    public static function make(): static
    {
        return new static;
    }
}
