<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Address
 */
class AddressResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'line_one' => $this->line_one,
            'line_two' => $this->line_two,
            'line_three' => $this->line_three,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country ? [
                'name' => $this->country->name(),
                'code' => $this->country->value,
            ] : null,
        ];
    }
}
