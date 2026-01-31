<?php


namespace App\Translation;


use Closure;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Facades\App;
use JsonSerializable;
use Stringable;

final class TranslatableString implements Stringable, JsonSerializable, Arrayable
{
    /**
     * Custom resolution callback for current locale.
     */
    protected static ?Closure $resolveLocaleUsing = null;

    /**
     * Custom resolution callback for fallback locale.
     */
    protected static ?Closure $resolveFallbackUsing = null;

    public function __construct(
        protected readonly RawString|LocalizedString $value
    ) { }

    /**
     * Get the underlying value of the string.
     */
    public function unwrap(): RawString|LocalizedString
    {
        return $this->value;
    }

    /**
     * Determine whether value is localized.
     */
    public function localized(): bool
    {
        return $this->value instanceof LocalizedString;
    }

    /**
     * Determine whether this string is equal to other string.
     */
    public function equalTo(TranslatableString $other): bool
    {
        $first = $this->unwrap();
        $second = $other->unwrap();

        if ($first instanceof RawString && $second instanceof RawString) {
            return $first->equalTo($second);
        }

        if ($first instanceof LocalizedString && $second instanceof LocalizedString) {
            return $first->equalTo($second);
        }

        return false;
    }

    /**
     * Determine whether value is empty.
     */
    public function empty(): bool
    {
        return $this->value->empty();
    }

    /**
     * Set the value of the string.
     */
    public function set(RawString|LocalizedString|string|array $value): TranslatableString
    {
        $value = TranslatableString::wrapValue($value);

        if (get_class($this->value) !== get_class($value)) {
            return $this->replace($value);
        }

        if ($value instanceof RawString) {
            return $this->replace($value);
        }

        return new TranslatableString($this->value->merge($value));
    }

    /**
     * Replace value of the string with new one.
     */
    public function replace(RawString|LocalizedString|string|array $value): TranslatableString
    {
        return new TranslatableString(TranslatableString::wrapValue($value));
    }

    /**
     * Get the value of the string for current locale.
     */
    public function value(bool $strict = true): ?string
    {
        return $this->valueForLocale($this->getLocale(), $this->getFallbackLocale(), $strict);
    }

    /**
     * Get the value of the string for given locale.
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
    public function valueForLocale(string $locale, string|array|null $fallback = null, bool $strict = true): ?string
    {
        if ($this->value instanceof RawString) {
            return $this->value->value();
        }

        return $this->value->value($locale, $fallback, $strict);
    }

    /**
     * Get the current locale.
     */
    public function getLocale(): string
    {
        if (TranslatableString::$resolveLocaleUsing instanceof Closure) {
            if ($locale = call_user_func(TranslatableString::$resolveLocaleUsing)) {
                return $locale;
            }
        }

        return App::getLocale();
    }

    /**
     * Get the fallback locales.
     */
    public function getFallbackLocale(): array|string
    {
        if (TranslatableString::$resolveFallbackUsing instanceof Closure) {
            if ($fallback = call_user_func(TranslatableString::$resolveFallbackUsing, $this->getLocale())) {
                return $fallback;
            }
        }

        return App::getFallbackLocale();
    }

    /**
     * Get the value according to an integer value.
     */
    public function choice($number, bool $strict = true, array $replace = []): ?string
    {
        return $this->choiceForLocale($number, $this->getLocale(), $this->getFallbackLocale(), $strict, $replace);
    }

    /**
     * Get the value according to an integer value for given locale.
     */
    public function choiceForLocale($number, string $locale, string|array|null $fallback = null, bool $strict = true, array $replace = []): ?string
    {
        if ($this->value instanceof RawString) {
            return $this->value->choice($number, $locale, $replace);
        }

        return $this->value->choice($number, $locale, $fallback, $strict, $replace);
    }

    /**
     * Create new instance of the translatable string from underlying value.
     */
    public static function make(RawString|LocalizedString|string|array|null $value, bool $tryJsonString = false): ?TranslatableString
    {
        if (is_array($value)) {
            $value = new LocalizedString($value);
        } else if (is_string($value)) {
            if ($tryJsonString && ($decoded = json_decode($value, true))) {
                $value = new LocalizedString($decoded);
            } else {
                $value = new RawString($value);
            }
        }

        if ($value instanceof RawString || $value instanceof LocalizedString) {
            return new TranslatableString($value);
        }

        return null;
    }

    /**
     * Get the string value for current locale.
     */
    public function __toString(): string
    {
        return $this->value() ?: "";
    }

    /**
     * Set custom resolution callback for current locale.
     */
    public static function resolveLocaleUsing(?Closure $using): void
    {
        TranslatableString::$resolveLocaleUsing = $using;
    }

    /**
     * Set custom resolution callback for current fallback locale.
     */
    public static function resolveFallbackLocaleUsing(?Closure $using): void
    {
        TranslatableString::$resolveFallbackUsing = $using;
    }

    /**
     * Wrap the given value in TranslatableString if applicable.
     */
    public static function wrap(string|array|LocalizedString|RawString|TranslatableString $value): TranslatableString
    {
        if ($value instanceof TranslatableString) {
            return $value;
        }

        return new TranslatableString(TranslatableString::wrapValue($value));
    }

    /**
     * Wrap the given value in LocalizedString or RawString if applicable.
     */
    protected static function wrapValue(string|array|LocalizedString|RawString $value): LocalizedString|RawString
    {
        if (is_array($value) || $value instanceof LocalizedString) {
            return LocalizedString::wrap($value);
        }

        return RawString::wrap($value);
    }

    /**
     * Create new instance of the string from array.
     */
    public static function fromArray(?array $value): ?TranslatableString
    {
        if (empty($value)) {
            return null;
        }

        $keys = array_keys($value);

        if (count($keys) === 1 && $keys[0] === 'value') {
            return new TranslatableString(new RawString($value['value']));
        }

        return new TranslatableString(new LocalizedString($value));
    }

    /**
     * Get raw value of the string.
     */
    public function toArray(): array
    {
        if ($this->value instanceof RawString) {
            return ['value' => $this->value->value()];
        }

        return $this->value->all();
    }

    public function jsonSerialize(): mixed
    {
        return $this->toArray();
    }

    /**
     * Determine whether given two strings are equal.
     */
    public static function areEqual(?TranslatableString $first, ?TranslatableString $second): bool
    {
        if (!$first && !$second) {
            return true;
        }

        if (!$first || !$second) {
            return false;
        }

        return $first->equalTo($second);
    }
}
