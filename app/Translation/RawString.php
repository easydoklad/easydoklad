<?php


namespace App\Translation;


use Illuminate\Translation\MessageSelector;

final class RawString
{
    public function __construct(
        protected readonly ?string $value = null
    ) { }

    /**
     * Get the value of the string.
     */
    public function value(): ?string
    {
        return $this->value;
    }

    /**
     * Get the value according to an integer value.
     */
    public function choice($number, string $locale, array $replace = []): ?string
    {
        if (! $this->value) {
            return null;
        }

        if (is_countable($number)) {
            $number = count($number);
        }

        if (! isset($replace['count'])) {
            $replace['count'] = $number;
        }

        $replacer = new PlaceholderReplacer;
        $selector = new MessageSelector;

        return $selector->choose(
            $replacer->makeReplacements($this->value, $replace),
            $number,
            $locale
        );
    }

    /**
     * Check whether two strings are equal.
     */
    public function equalTo(RawString $other): bool
    {
        return $this->value === $other->value;
    }

    /**
     * Determine whether string is empty.
     */
    public function empty(): bool
    {
        return $this->value === null || $this->value === "";
    }

    /**
     * Wrap the given value in RawString if applicable.
     */
    public static function wrap(RawString|string $value): RawString
    {
        return $value instanceof RawString ? $value : new RawString($value);
    }
}
