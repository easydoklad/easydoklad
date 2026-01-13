<?php

namespace Database\Factories;

use App\Support\MoneyUtils;
use Brick\Math\BigNumber;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\InvoiceLine>
 */
class InvoiceLineFactory extends Factory
{
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->sentence,
            'quantity' => 1,
            'unit' => 'ks',
            'position' => 1,
            'vat_rate' => null,
            'unit_price_vat_exclusive' => 10000,
            'total_price_vat_exclusive' => 10000,
            'total_price_vat_inclusive' => null,
            'currency' => 'EUR',
        ];
    }

    public function withVatExclusivePrice(int $unitPrice, BigNumber|float|int|null $vatRate = null, int $quantity = 1): static
    {
        return $this->state(function () use ($unitPrice, $vatRate, $quantity) {
            $totalPrice = Money::ofMinor($unitPrice * $quantity, 'EUR');
            $totalVatInclusivePrice = null;

            if ($vatRate) {
                [$totalVatInclusivePrice] = MoneyUtils::addPercent($totalPrice, $vatRate);
            }

            return [
                'quantity' => $quantity,
                'unit_price_vat_exclusive' => $unitPrice,
                'vat_rate' => $vatRate,
                'total_price_vat_exclusive' => $totalPrice,
                'total_price_vat_inclusive' => $totalVatInclusivePrice,
            ];
        });
    }

    public function withVatInclusivePrice(int $unitPrice, BigNumber|float|int $vatRate, int $quantity = 1): static
    {
        return $this->state(function () use ($unitPrice, $vatRate, $quantity) {
            $vatInclusiveUnitPrice = Money::ofMinor($unitPrice, 'EUR');
            [$vatExclusiveUnitPrice] = MoneyUtils::subPercent($vatInclusiveUnitPrice, $vatRate);

            return [
                'quantity' => $quantity,
                'unit_price_vat_exclusive' => $vatExclusiveUnitPrice,
                'vat_rate' => $vatRate,
                'total_price_vat_exclusive' => $vatExclusiveUnitPrice->multipliedBy($quantity),
                'total_price_vat_inclusive' => $vatInclusiveUnitPrice->multipliedBy($quantity),
            ];
        });
    }
}
