<?php

namespace Database\Factories;

use App\Models\Address;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Company>
 */
class CompanyFactory extends Factory
{
    public function definition(): array
    {
        return [
            'business_name' => fake()->company,
            'business_id' => Str::upper(Str::random(10)),
            'vat_id' => Str::upper(Str::random(10)),
            'eu_vat_id' => Str::upper(Str::random(10)),
            'email' => fake()->email,
            'website' => fake()->url,
            'phone_number' => fake()->phoneNumber,
            'bank_name' => fake()->company,
            'bank_address' => fake()->address,
            'bank_bic' => Str::upper(Str::random(6)),
            'bank_account_iban' => fake()->iban,
        ];
    }

    public function withAddress(): static
    {
        return $this->for(Address::factory());
    }
}
