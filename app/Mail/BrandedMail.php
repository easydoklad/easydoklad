<?php


namespace App\Mail;


use App\Support\SafeMarkdownConverter;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

abstract class BrandedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $theme = 'themes.branded';

    public function __construct(
        public MailBranding $branding,
    ) { }

    public function envelope(): Envelope
    {
        return new Envelope(
            from: new Address(
                address: $this->branding->mailFromAddress,
                name: $this->branding->mailFromName,
            ),
            cc: $this->branding->mailCc ?: [],
            bcc: $this->branding->mailBcc ?: [],
            replyTo: $this->branding->mailReplyTo ?: [],
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.branded',
        );
    }
}
