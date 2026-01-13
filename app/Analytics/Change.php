<?php

namespace App\Analytics;

final readonly class Change
{
    public function __construct(
        public mixed $value,
        public int|float $percentage,
    ) {}

    /**
     * Vráti aký je trend pre túto zmenu.
     */
    public function trend(): Trend
    {
        if ($this->percentage > 0) {
            return Trend::Increasing;
        } elseif ($this->percentage < 0) {
            return Trend::Decreasing;
        } else {
            return Trend::None;
        }
    }
}
