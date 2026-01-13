<?php

namespace App\Support;

use Illuminate\Support\Str;

final readonly class Locale
{
    /**
     * Get name of the locale.
     */
    public static function name(string $locale): string
    {
        return match ($locale) {
            'sk' => 'Slovenský',
            'en' => 'Anglický',
            'de' => 'Nemecký',
            default => Str::upper($locale),
        };
    }
}
