<?php


namespace App\Translation;


use Closure;
use Illuminate\Support\Arr;
use Illuminate\Translation\MessageSelector;

final readonly class LocalizedString
{
    /**
     * Create new instance of localized string.
     *
     * @param array<string, string> $value
     */
    public function __construct(
        protected array $value = []
    ) { }

    /**
     * Get the list of locales where to which the string is localized.
     *
     * @return array<string>
     */
    public function locales(): array
    {
        return array_keys($this->value);
    }

    /**
     * Call given closure over each localized value.
     */
    public function each(Closure $closure): LocalizedString
    {
        collect($this->value)->each($closure);

        return $this;
    }

    /**
     * Check whether two strings are equal.
     */
    public function equalTo(LocalizedString $other): bool
    {
        $thisLocales = array_keys($this->value);
        sort($thisLocales);

        $otherLocales = array_keys($other->value);
        sort($otherLocales);

        if ($thisLocales != $otherLocales) {
            return false;
        }

        return collect($thisLocales)->every(
            fn (string $locale) => Arr::get($this->value, $locale) === Arr::get($other->value, $locale)
        );
    }

    /**
     * Retrieve all localizations of the string.
     */
    public function all(): array
    {
        return $this->value;
    }

    /**
     * Determine whether localized string is empty.
     */
    public function empty(): bool
    {
        return empty($this->value) || collect($this->value)->every(fn ($value) => $value === null || $value === "");
    }

    /**
     * Merge the current localized string with another.
     *
     * If a locale exists in both instances, its value in the current object
     * will be replaced by the corresponding value from the given object.
     *
     * @param  LocalizedString|array  $value  The localized string or array to merge.
     * @return LocalizedString                A new instance of the string with merged values.
     */
    public function merge(LocalizedString|array $value): LocalizedString
    {
        $value = LocalizedString::wrap($value);

        /** @var array<string, string> $raw */
        $raw = [...$this->value];

        foreach ($value->value as $locale => $val) {
            Arr::set($raw, $locale, $val);
        }

        return new LocalizedString($raw);
    }

    /**
     * Get the value for the given locale.
     *
     * If the value for the given locale is not localized, the fallback locale(s) will be used.
     * Value is considered localized when it is not empty or null. When strict mode is enabled,
     * a value is considered localized if it is present, regardless of whether it is empty or null.
     *
     * @param string $locale The locale key.
     * @param string|array|null $fallback The fallback locale key or an array of fallbacks.
     * @param bool $strict Whether to enforce strict localization rules.
     * @return string|null
     */
    public function value(string $locale, string|array|null $fallback = null, bool $strict = true): ?string
    {
        $value = $this->resolveValueWithLocale($locale, $fallback, $strict);

        if (is_array($value)) {
            return $value[0];
        }

        return null;
    }

    /**
     * Resolve final value and locale.
     */
    protected function resolveValueWithLocale(string $locale, string|array|null $fallback = null, bool $strict = true): ?array
    {
        if (Arr::has($this->value, $locale)) {
            $localizedValue = $this->value[$locale];

            // In strict mode, return the value if the locale exists, regardless of its content.
            if ($strict) {
                return [$localizedValue, $locale];
            }

            // Return the value if it's not null or an empty string.
            if (!is_null($localizedValue) && $localizedValue !== "") {
                return [$localizedValue, $locale];
            }
        }

        if (is_null($fallback)) {
            return null;
        }

        foreach (Arr::wrap($fallback) as $fallbackLocale) {
            if (Arr::has($this->value, $fallbackLocale)) {
                $fallbackValue = $this->value[$fallbackLocale];

                // In strict mode, return the fallback value if it exists.
                if ($strict) {
                    return [$fallbackValue, $fallbackLocale];
                }

                // Return the fallback value if it's not null or an empty string.
                if (!is_null($fallbackValue) && $fallbackValue !== "") {
                    return [$fallbackValue, $fallbackLocale];
                }
            }
        }

        return null;
    }

    /**
     * Get a translation according to an integer value.
     */
    public function choice($number, string $locale, string|array|null $fallback = null, bool $strict = true, array $replace = []): ?string
    {
        $valueWithLocale = $this->resolveValueWithLocale($locale, $fallback, $strict);

        if (is_null($valueWithLocale)) {
            return null;
        }

        [$line, $locale] = $valueWithLocale;

        if (is_countable($number)) {
            $number = count($number);
        }

        if (! isset($replace['count'])) {
            $replace['count'] = $number;
        }

        $replacer = new PlaceholderReplacer;
        $selector = new MessageSelector;

        return $selector->choose(
            $replacer->makeReplacements($line, $replace),
            $number,
            $locale
        );
    }

    /**
     * Wrap the given value in LocalizedString if applicable.
     */
    public static function wrap(LocalizedString|array $value): LocalizedString
    {
        return $value instanceof LocalizedString ? $value : new LocalizedString($value);
    }
}
