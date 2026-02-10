<?php

namespace App\Http\Resources;

use App\Enums\PaymentMethod;
use App\Support\VatBreakdownLine;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Models\Invoice
 */
class InvoiceResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->uuid,
            'is_draft' => $this->draft,
            'is_locked' => $this->locked,
            'is_sent' => $this->sent,
            'is_paid' => $this->paid,
            'issued_at' => $this->issued_at?->format('Y-m-d'),
            'supplied_at' => $this->supplied_at?->format('Y-m-d'),
            'payment_due_to' => $this->payment_due_to?->format('Y-m-d'),
            'supplier' => new CompanyResource($this->supplier),
            'customer' => new CompanyResource($this->customer),
            'invoice_number' => $this->public_invoice_number,
            'number_sequence' => $this->numberSequence ? [
                'format' => $this->numberSequence->format,
                'number' => $this->invoice_number,
            ] : null,
            'payment_method' => $this->payment_method,
            'bank_transfer_info' => $this->payment_method === PaymentMethod::BankTransfer ? [
                'bank_name' => $this->supplier->bank_name,
                'bank_address' => $this->supplier->bank_address,
                'bank_bic' => $this->supplier->bank_bic,
                'bank_account_iban' => $this->supplier->bank_account_iban,
                'bank_account_number' => $this->supplier->bank_account_number,
            ] : null,
            'variable_symbol' => $this->variable_symbol,
            'specific_symbol' => $this->specific_symbol,
            'constant_symbol' => $this->constant_symbol,
            'currency' => $this->currency,
            'total_vat_exclusive' => $this->total_vat_exclusive?->getMinorAmount()->toInt(),
            'total_vat_inclusive' => $this->total_vat_inclusive?->getMinorAmount()->toInt(),
            'vat_amount' => $this->getVatAmount()?->getMinorAmount()->toInt(),
            'total_to_pay' => $this->total_to_pay?->getMinorAmount()->toInt(),
            'remaining_to_pay' => $this->remaining_to_pay?->getMinorAmount()->toInt(),
            'vat_breakdown' => $this->getVatBreakdown()->map(fn (VatBreakdownLine $line) => [
                'rate' => $line->rate->toFloat(),
                'base' => $line->base->getMinorAmount()->toInt(),
                'total' => $line->vat->getMinorAmount()->toInt(),
            ]),
            'issued_by' => $this->issued_by,
            'issued_by_email' => $this->issued_by_email,
            'issued_by_phone_number' => $this->issued_by_phone_number,
            'issued_by_website' => $this->issued_by_website,
            'vat_enabled' => $this->vat_enabled,
            'vat_reverse_charge' => $this->vat_reverse_charge,
            'show_pay_by_square' => $this->show_pay_by_square,
            'footer_note' => $this->footer_note,
            'template' => new DocumentTemplateResource($this->template),
            'lines' => InvoiceLineResource::collection($this->getSortedLines()),
            'payments' => PaymentResource::collection($this->payments),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }
}
