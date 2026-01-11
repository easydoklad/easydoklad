<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Company
 */
class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'business_name' => $this->business_name,
            'business_id' => $this->business_id,
            'vat_id' => $this->vat_id,
            'eu_vat_id' => $this->eu_vat_id,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'website' => $this->website,
            'additional_info' => $this->additional_info,
            'address' => new AddressResource($this->address),
        ];
    }
}
