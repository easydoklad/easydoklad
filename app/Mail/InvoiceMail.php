<?php

namespace App\Mail;

use App\Models\Invoice;
use App\Translation\PlaceholderReplacer;
use Illuminate\Mail\Mailables\Attachment;

class InvoiceMail extends BrandedMail
{
    public function __construct(
        MailBranding   $branding,
        public Invoice $invoice,
        public string  $message,
        public string  $moneyFormattingLocale,
    )
    {
        parent::__construct($branding);
    }

    public function attachments(): array
    {
        return [
            Attachment::fromData(
                data: fn () => $this->invoice->renderToPdf($this->locale, $this->moneyFormattingLocale),
                name: $this->invoice->createFileName($this->locale, 'pdf'),
            )->withMime('application/pdf'),
        ];
    }

    /**
     * Create a new instance of the mail.
     */
    public static function make(Invoice $invoice, ?string $message = null, ?string $locale = null): static
    {
        $account = $invoice->account;
        $locale = $locale ?: $account->getPreferredDocumentLocale();

        $config = $invoice->account->getMailConfiguration();

        $markdownReplcements = $invoice->getMarkdownReplacements();
        $replacements = $markdownReplcements->all();
        $replacer = new PlaceholderReplacer;

        $messageContent = $replacer->makeReplacements(($message ?: $config->invoiceSentMessage()->valueForLocale($locale)) ?: '', $replacements);
        $subjectContent = $replacer->makeReplacements($config->invoiceSentSubject()->valueForLocale($locale) ?: '', $replacements);

        $branding = MailBranding::forAccount($account, $locale, $markdownReplcements);

        $mail = new static(
            branding: $branding,
            invoice: $invoice,
            message: $messageContent,
            moneyFormattingLocale: $account->getMoneyFormattingLocale(),
        );

        return $mail->locale($locale)->subject($subjectContent);
    }
}
