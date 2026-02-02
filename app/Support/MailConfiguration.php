<?php


namespace App\Support;


use App\Translation\LocalizedString;
use App\Translation\RawString;
use App\Translation\TranslatableString;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Database\Eloquent\Casts\Json;
use Illuminate\Support\Facades\Crypt;
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

    protected function encryptedArray(string $key, ?array $default = null): ?array
    {
        if ($this->config->has($key)) {
            $value = $this->config->string($key)->value();

            return Json::decode(Crypt::decryptString($value));
        }

        return $default;
    }

    protected function setEncryptedArray(string $key, ?array $value): static
    {
        if ($value) {
            $this->config->set($key, Crypt::encryptString(Json::encode($value)));
        } else if ($this->config->has($key)) {
            unset($this->config[$key]);
        }

        return $this;
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

    protected function setLocalized(string $key, ?TranslatableString $content, bool $allowNull = false): static
    {
        $value = $this->localized($key);

        if (TranslatableString::areEqual($value, $content) && !$allowNull) {
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

    public function sender(): string
    {
        return $this->config->string(
            key: 'sender',
            default: 'system',
        );
    }

    public function setSender(string $value): static
    {
        if ($this->sender() !== $value) {
            $this->config->set('sender', $value);
        }

        return $this;
    }

    public function senderName(): ?string
    {
        return $this->config->string(key: 'sender_name');
    }

    public function setSenderName(?string $value): static
    {
        if ($value) {
            $this->config->set('sender_name', $value);
        } else if ($this->config->has('sender_name')) {
            unset($this->config['sender_name']);
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
        return $this->setLocalized('footer_content', $content, allowNull: true);
    }

    public function header(): ?TranslatableString
    {
        return $this->localized(
            key: 'header_content',
            default: TranslatableString::make(new RawString(':supplier.business_name'))
        );
    }

    public function setHeader(?TranslatableString $value): static
    {
        return $this->setLocalized('header_content', $value, allowNull: true);
    }

    public function showHeaderLogo(): bool
    {
        return $this->config->boolean(
            key: 'show_header_logo',
            default: true,
        );
    }

    public function setShowHeaderLogo(bool $show): static
    {
        if ($this->showHeaderLogo() !== $show) {
            $this->config->set('show_header_logo', $show);
        }

        return $this;
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

    public function mailer(): ?array
    {
        return $this->encryptedArray('mailer');
    }

    public function setMailer(?array $config): static
    {
        return $this->setEncryptedArray('mailer', $config);
    }

    public function carbonCopy(): array
    {
        if ($this->config->has('carbon_copy')) {
            return $this->config->array('carbon_copy');
        }

        return [];
    }

    public function setCarbonCopy(array $value): static
    {
        if (empty($value)) {
            unset($this->config['carbon_copy']);
        } else {
            $this->config->set('carbon_copy', $value);
        }

        return $this;
    }

    public function blindCarbonCopy(): array
    {
        if ($this->config->has('blind_carbon_copy')) {
            return $this->config->array('blind_carbon_copy');
        }

        return [];
    }

    public function setBlindCarbonCopy(array $value): static
    {
        if (empty($value)) {
            unset($this->config['blind_carbon_copy']);
        } else {
            $this->config->set('blind_carbon_copy', $value);
        }

        return $this;
    }

    public function replyTo(): array
    {
        if ($this->config->has('reply_to')) {
            return $this->config->array('reply_to');
        }

        return [];
    }

    public function setReplyTo(array $value): static
    {
        if (empty($value)) {
            unset($this->config['reply_to']);
        } else {
            $this->config->set('reply_to', $value);
        }

        return $this;
    }

    public function toArray(): array
    {
        return $this->config->toArray();
    }
}
