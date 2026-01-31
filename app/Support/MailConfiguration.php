<?php


namespace App\Support;


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

    public function font(): string
    {
        return $this->config->string(
            key: 'font',
            default: self::FONT_ARIAL,
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
        if ($this->config->has('footer_content')) {
            if ($value = $this->config->array('footer_content')) {
                return TranslatableString::fromArray($value);
            }

            return null;
        }

        return TranslatableString::make(new RawString(
            '{{ dodavatel.nazov }}, {{ dodavatel.adresa }}, {{ dodavatel.identifikatory }}'.PHP_EOL.'Tento e-mail bol vygenerovaný automaticky.'
        ));
    }

    public function setFooter(?TranslatableString $content): static
    {
        $value = $this->footer();

        if (TranslatableString::areEqual($value, $content)) {
            return $this;
        }

        $this->config->set('footer_content', $content?->toArray());

        return $this;
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
