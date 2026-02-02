<?php


namespace App\Mail;


use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;

abstract class BrandedMail extends Mailable
{
    use SerializesModels;

    public $theme = 'themes.branded';

    public function __construct(
        public MailBranding $branding,
    ) { }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.branded',
        );
    }
}
