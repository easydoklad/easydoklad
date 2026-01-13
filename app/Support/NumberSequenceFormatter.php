<?php

namespace App\Support;

use Carbon\Carbon;
use Illuminate\Support\Str;

class NumberSequenceFormatter
{
    public function __construct(
        protected string $format,
        protected Carbon $date,
    ) {}

    /**
     * Get the format the formatter is using.
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Replace date and number tokens in given format.
     */
    protected function replaceTokens(string $format, ?int $number = null): string
    {
        // Escape double backslash
        $format = Str::replace('\\\\', "\x00", $format);

        // Escape fixed tokens
        $fixedTokens = [
            '\RRRR' => "\x01",
            '\RR' => "\x02",
            '\MM' => "\x03",
        ];
        foreach ($fixedTokens as $token => $replacement) {
            $format = Str::replace($token, $replacement, $format);
        }

        // Escape arbitrary C tokens
        $arbitraryEscapeMap = [];
        $format = Str::replaceMatches('/\\\\C{1,20}/', function (array $matches) use (&$arbitraryEscapeMap) {
            $key = '__'.count($arbitraryEscapeMap).'__';
            $arbitraryEscapeMap[$key] = Str::ltrim($matches[0], '\\');

            return $key;
        }, $format);

        // Replace date tokens
        $year = $this->date->format('Y');
        $month = $this->date->format('m');
        $dateReplacements = [
            'RRRR' => $year,
            'RR' => Str::substr($year, 2, 2),
            'MM' => Str::padLeft($month, 2, '0'),
        ];

        $format = Str::replaceMatches(
            '/RRRR|RR|MM/',
            fn (array $matches) => $dateReplacements[$matches[0]],
            $format
        );

        // Replace number tokens
        if ($number !== null) {
            $format = Str::replaceMatches(
                '/C{1,20}/',
                fn (array $matches) => Str::padLeft("{$number}", Str::length($matches[0]), '0'),
                $format
            );
        }

        // Restore escaped fixed tokens
        foreach ($fixedTokens as $replacement => $token) {
            $format = Str::replace($token, Str::ltrim($replacement, '\\'), $format);
        }

        // Restore double backslash
        $format = Str::replace("\x00", '\\', $format);

        // Restore arbitrary C tokens
        foreach ($arbitraryEscapeMap as $token => $replacement) {
            $format = Str::replace($token, $replacement, $format);
        }

        return $format;
    }

    /**
     * Create sequence token for given format.
     */
    public function formatSequenceToken(): string
    {
        return $this->replaceTokens($this->format);
    }

    /**
     * Format the given number.
     */
    public function formatNumber(int $number): string
    {
        return $this->replaceTokens($this->format, $number);
    }
}
