<?php

namespace Database\Factories;

use App\Enums\Country;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    public function definition(): array
    {
        return [
            'line_one' => fake()->streetAddress,
            'postal_code' => fake()->postcode,
            'city' => fake()->city,
            'country' => $this->faker->randomElement(Country::cases()),
        ];
    }
}
