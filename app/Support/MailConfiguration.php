<?php


namespace App\Support;


use App\Translation\LocalizedString;
use App\Translation\RawString;
use App\Translation\TranslatableString;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Fluent;

class MailConfiguration implements Arrayable
{
    const ALIGN_LEFT = 'left';
    const ALIGN_CENTER = 'center';

    const FONT_ARIAL = 'arial';
    const FONT_HELVETICA = 'helvetica';
    const FONT_GEORGIA = 'georgia';
    const FONT_TIMES_NEW_ROMAN = 'timesnewroman';

    /**
     * Underlying mail configuration.
     */
    protected Fluent $config;

    public function __construct(array $data)
    {
        $this->config = new Fluent($data);
    }

    protected function localized(string $key, ?TranslatableString $default = null): ?TranslatableString
    {
        if ($this->config->has($key)) {
            if ($value = $this->config->array($key)) {
                return TranslatableString::fromArray($value);
            }

            return null;
        }

        return $default;
    }

    protected function setLocalized(string $key, ?TranslatableString $content): static
    {
        $value = $this->localized($key);

        if (TranslatableString::areEqual($value, $content)) {
            return $this;
        }

        $this->config->set($key, $content?->toArray());

        return $this;
    }

    public function font(): string
    {
        return $this->config->string(
            key: 'font',
            default: self::FONT_HELVETICA,
        );
    }

    public function setFont(string $font): static
    {
        if ($this->font() !== $font) {
            $this->config->set('font', $font);
        }

        return $this;
    }

    public function footer(): ?TranslatableString
    {
        return $this->localized(
            key: 'footer_content',
            default: TranslatableString::make(new LocalizedString([
                'sk' => '**:supplier.business_name**  '.PHP_EOL.':supplier.address  '.PHP_EOL.':supplier.identifiers  '.PHP_EOL.PHP_EOL.'Tento e-mail bol vygenerovaný automaticky.',
                'en' => '**:supplier.business_name**  '.PHP_EOL.':supplier.address  '.PHP_EOL.':supplier.identifiers  '.PHP_EOL.PHP_EOL.'This e-mail was generated automatically.',
                'de' => '**:supplier.business_name**  '.PHP_EOL.':supplier.address  '.PHP_EOL.':supplier.identifiers  '.PHP_EOL.PHP_EOL.'Diese E-Mail wurde automatisch generiert.',
            ]))
        );
    }

    public function setFooter(?TranslatableString $content): static
    {
        return $this->setLocalized('footer_content', $content);
    }

    public function header(): TranslatableString
    {
        return $this->localized(
            key: 'header_content',
            default: TranslatableString::make(new RawString(':supplier.business_name'))
        );
    }

    public function setHeader(TranslatableString $value): static
    {
        return $this->setLocalized('header_content', $value);
    }

    public function invoiceSentSubject(): TranslatableString
    {
        return $this->localized(
            key: 'invoice_sent_subject',
            default: TranslatableString::make(new LocalizedString([
                'sk' => 'Faktúra č.:invoice.number | :supplier.business_name',
                'en' => 'Invoice :invoice.number | :supplier.business_name',
                'de' => 'Rechnung :invoice.number | :supplier.business_name',
            ]))
        );
    }

    public function setInvoiceSentSubject(TranslatableString $value): static
    {
        return $this->setLocalized('invoice_sent_subject', $value);
    }

    public function invoiceSentMessage(): TranslatableString
    {
        return $this->localized(
            key: 'invoice_sent_message',
            default: TranslatableString::make(new LocalizedString([
                'sk' => <<<'MAIL'
Vážený klient,

v prílohe Vám zasielame elektronickú faktúru č.**:invoice.number**.

V prípade akýchkoľvek otázok nás neváhajte kontaktovať odpoveďou na tento e-mail.

Ďakujeme za prejavenú dôveru!
MAIL
,
                'en' => <<<'MAIL'
Dear Customer,

please find attached electronic invoice no. **:invoice.number** as an attachment to this email.

If you have any questions, please do not hesitate to contact us by replying to this email.

Thank you for your business!
MAIL
,
                'de' => <<<'MAIL'
Sehr geehrte Kundin, sehr geehrter Kunde,

anbei erhalten Sie die elektronische Rechnung Nr. **:invoice.number** als Anhang zu dieser E-Mail.

Falls Sie Fragen haben, zögern Sie bitte nicht, uns durch eine Antwort auf diese E-Mail zu kontaktieren.

Vielen Dank für Ihr Vertrauen!
MAIL
                ,
            ]))
        );
    }

    public function setInvoiceSentMessage(TranslatableString $content): static
    {
        return $this->setLocalized('invoice_sent_message', $content);
    }

    public function alignment(): string
    {
        return $this->config->string(
            key: 'alignment',
            default: self::ALIGN_LEFT,
        );
    }

    public function setAlignment(string $alignment): static
    {
        if ($this->alignment() !== $alignment) {
            $this->config->set('alignment', $alignment);
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->config->toArray();
    }
}
