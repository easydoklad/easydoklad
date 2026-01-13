<?php

namespace App\Models;

use App\Casts\AsMoney;
use App\Models\Concerns\HasUuid;
use Brick\Money\Money;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $position
 * @property \App\Models\Invoice $invoice
 * @property string|null $title
 * @property string|null $description
 * @property string|null $unit
 * @property float|null $quantity
 * @property float|null $vat_rate
 * @property \Brick\Money\Money|null $unit_price_vat_exclusive
 * @property \Brick\Money\Money|null $total_price_vat_inclusive
 * @property \Brick\Money\Money|null $total_price_vat_exclusive
 */
class InvoiceLine extends Model
{
    /** @use HasFactory<\Database\Factories\InvoiceLineFactory> */
    use HasFactory, HasUuid;

    protected $guarded = false;

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'vat_rate' => 'float',
            'unit_price_vat_exclusive' => AsMoney::class,
            'total_price_vat_inclusive' => AsMoney::class,
            'total_price_vat_exclusive' => AsMoney::class,
        ];
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class);
    }

    /**
     * Get the VAT amount of the line.
     */
    public function getVatAmount(): ?Money
    {
        if ($this->total_price_vat_exclusive && $this->total_price_vat_inclusive) {
            return $this->total_price_vat_inclusive->minus($this->total_price_vat_exclusive);
        }

        return null;
    }
}
