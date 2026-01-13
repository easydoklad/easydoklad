<?php

namespace Database\Factories;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use App\Models\Company;
use App\Models\Invoice;
use App\Models\InvoiceLine;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Invoice>
 */
class InvoiceFactory extends Factory
{
    public function definition(): array
    {
        return [
            'draft' => true,
            'sent' => true,
            'paid' => false,
            'locked' => false,
            'payment_method' => PaymentMethod::BankTransfer,
            'currency' => 'EUR',
            'vat_enabled' => true,
            'footer_note' => 'Spoločnosť je zapísaná v obchodnom registri',
            'vat_reverse_charge' => false,
            'show_pay_by_square' => true,
            'issued_at' => now(),
            'supplied_at' => now(),
            'payment_due_to' => now()->addDays(13),
        ];
    }

    public function withSupplier(): static
    {
        return $this->for(Company::factory()->withAddress(), 'supplier');
    }

    public function withCustomer(): static
    {
        return $this->for(Company::factory()->withAddress(), 'customer');
    }

    public function withDefaultTemplate(): static
    {
        return $this->for(getDefaultTemplate(DocumentType::Invoice), 'template');
    }

    public function withLines(int $count = 1): static
    {
        return $this->afterCreating(function (Invoice $invoice) use ($count) {
            for ($i = 0; $i < $count; $i++) {
                $line = $invoice->vat_enabled
                    ? InvoiceLine::factory()->withVatInclusivePrice(15000, 20)
                    : InvoiceLine::factory()->withVatExclusivePrice(15000);

                $line->for($invoice)->create([
                    'position' => $i + 1,
                ]);
            }

            $invoice->calculateTotals();
        });
    }
}
