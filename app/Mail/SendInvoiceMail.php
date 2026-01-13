<?php

namespace App\Mail;

use App\Models\Invoice;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class SendInvoiceMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Invoice $invoice,
        public string $message,
        public string $invoiceLocale,
        public string $moneyFormattingLocale,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: config('mail.from.address'),
                name: $this->invoice->supplier->business_name ?: $this->invoice->account->company->business_name,
            ),
            replyTo: $this->invoice->supplier->email ?: $this->invoice->account->company->email,
            subject: "Faktúra {$this->invoice->public_invoice_number}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.sendInvoice',
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                data: fn () => $this->invoice->renderToPdf($this->invoiceLocale, $this->moneyFormattingLocale),
                name: $this->invoice->createFileName($this->invoiceLocale, 'pdf'),
            )->withMime('application/pdf'),
        ];
    }
}
