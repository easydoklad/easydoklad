<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Payment
 */
class PaymentResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'amount' => $this->amount->getMinorAmount()->toInt(),
            'currency' => $this->amount->getCurrency()->getCurrencyCode(),
            'payment_method' => $this->method,
            'received_at' => $this->received_at,
            'created_at' => $this->created_at,
        ];
    }
}
