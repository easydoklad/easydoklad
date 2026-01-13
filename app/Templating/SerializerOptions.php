<?php

namespace App\Templating;

final readonly class SerializerOptions
{
    public function __construct(
        public string $locale,
        public string $moneyFormattingLocale
    ) {}
}
