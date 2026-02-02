<?php

namespace Database\Factories;

use App\Enums\PaymentMethod;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Account>
 */
class AccountFactory extends Factory
{
    public function definition(): array
    {
        return [
            'vat_enabled' => true,
            'default_vat_rate' => 23,
            'invoice_due_days' => 14,
            'invoice_numbering_format' => 'RRRRMMCCCC',
            'invoice_variable_symbol_format' => 'RRRRMMCCCC',
            'invoice_payment_method' => PaymentMethod::BankTransfer,
            'invoice_footer_note' => fake()->sentence,
        ];
    }
}
