<?php

namespace App\Support;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;

final readonly class Locale
{
    public function __construct(
        public string $code,
        public string $name,
    ) { }

    /**
     * Get name of the locale.
     */
    public static function name(string $code): string
    {
        return match ($code) {
            'sk' => 'Slovenský',
            'en' => 'Anglický',
            'de' => 'Nemecký',
            default => Str::upper($code),
        };
    }

    /**
     * Get the list of available locales.
     *
     * @return \Illuminate\Support\Collection<int, \App\Support\Locale>
     */
    public static function all(): Collection
    {
        return collect([
            new Locale('sk', 'Slovenčina'),
            new Locale('cs', 'Čeština'),
            new Locale('en', 'Angličtina'),
            new Locale('de', 'Nemčina'),
        ]);
    }

    /**
     * Get list of available locale codes.
     *
     * @return array<string>
     */
    public static function codes(): array
    {
        return Locale::all()->map(fn (Locale $locale) => $locale->code)->all();
    }
}
