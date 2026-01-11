<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\InvoiceLine
 */
class InvoiceLineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'position' => $this->position,
            'title' => $this->title,
            'description' => $this->description,
            'unit_of_measure' => $this->unit,
            'quantity' => $this->quantity,
            'vat_rate' => $this->vat_rate,
            'currency' => $this->currency,
            'unit_price_vat_exclusive' => $this->unit_price_vat_exclusive?->getMinorAmount()->toInt(),
            'total_price_vat_exclusive' => $this->total_price_vat_exclusive?->getMinorAmount()->toInt(),
            'total_price_vat_inclusive' => $this->total_price_vat_inclusive?->getMinorAmount()->toInt(),
            'vat_amount' => $this->getVatAmount()?->getMinorAmount()->toInt(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
