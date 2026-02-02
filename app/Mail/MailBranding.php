<?php


namespace App\Mail;


use App\Models\Account;
use App\Support\MailConfiguration;
use App\Support\MarkdownReplacements;
use App\Translation\PlaceholderReplacer;

final readonly class MailBranding
{
    public function __construct(
        public ?string $footerContent,
        public string $font,
        public string $alignment,
        public ?string $headerContent,
        public ?string $headerLogo,
    ) { }

    public static function forAccount(Account $account, string $locale, ?MarkdownReplacements $replacements = null): MailBranding
    {
        $config = $account->getMailConfiguration();
        $replacements = $replacements ?: $account->getMarkdownReplacements();

        return MailBranding::make(
            locale: $locale,
            config: $config,
            replacements: $replacements,
            headerLogo: $account->wideLogo?->url(),
        );
    }

    public static function make(
        string $locale,
        MailConfiguration $config,
        ?MarkdownReplacements $replacements = null,
        ?string $headerLogo = null,
    ): MailBranding
    {
        $replacements = $replacements?->all() ?: [];
        $replacer = new PlaceholderReplacer;

        $headerContent = $replacer->makeReplacements($config->header()?->valueForLocale($locale) ?: '', $replacements);
        $footerContent = $replacer->makeReplacements($config->footer()?->valueForLocale($locale) ?: '', $replacements);

        return new MailBranding(
            footerContent: $footerContent,
            font: $config->font(),
            alignment: $config->alignment(),
            headerContent: $headerContent,
            headerLogo: $headerLogo,
        );
    }
}
