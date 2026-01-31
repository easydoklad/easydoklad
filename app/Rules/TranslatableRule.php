<?php


namespace App\Rules;


use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class TranslatableRule implements ValidationRule
{
    protected bool $required = false;

    /**
     * List of required locales.
     *
     * @var array<string>
     */
    protected array $requiredLocales = [];

    public function __construct(
        protected array $rules = []
    ) { }

    /**
     * When the value is localized, following locales must be present.
     */
    public function requireLocales(Collection|array $locales): static
    {
        $this->requiredLocales = Collection::wrap($locales)->all();

        return $this;
    }

    /**
     * The value under validation must be present, either as raw string or localized string.
     */
    public function required(bool $required = true): static
    {
        $this->required = $required;

        return $this;
    }

    /**
     * The value under validation can be null.
     */
    public function nullable(bool $nullable = true): static
    {
        return $this->required($nullable);
    }

    /**
     * Determine whether the value is empty.
     */
    protected function isEmpty(mixed $value): bool
    {
        if (! is_array($value)) {
            return true;
        }

        if (empty($value)) {
            return true;
        }

        if (collect(array_values($value))->every(fn ($val) => empty($val))) {
            return true;
        }

        return false;
    }

    /**
     * Determine whether the value is just single value.
     */
    protected function isRaw(mixed $value): bool
    {
        $keys = array_keys($value);

        return count($keys) === 1 && $keys[0] === 'value';
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($this->isEmpty($value)) {
            if ($this->required) {
                $fail('validation.required')->translate();
            }

            return;
        }


        // Raw String
        if ($this->isRaw($value)) {
            // Value is an object.
            if (Arr::isAssoc($this->rules)) {
                $validator = Validator::make($value['value'], $this->rules);
            } else {
                $rules = [$this->required ? 'required' : 'nullable', ...$this->rules];

                $safeAttribute = Str::replace('.', '%', $attribute);

                $validator = Validator::make(
                    data: [$safeAttribute => $value['value']],
                    rules: [$safeAttribute => $rules],
                    attributes: [$safeAttribute => $attribute],
                );
            }
        }
        // Localized String
        else {
            $locales = array_unique(array_merge(array_keys($value), $this->requiredLocales));

            $localeRules = collect($locales)->mapWithKeys(function (string $locale) {
                $rules = [in_array($locale, $this->requiredLocales) ? 'required' : ($this->required ? 'required' : 'nullable'), ...$this->rules];

                return [
                    $locale => $rules,
                ];
            })->all();

            $validator = Validator::make(
                data: $value,
                rules: $localeRules,
                attributes: collect($locales)->mapWithKeys(fn (string $locale) => [$locale => Str::upper($locale)])->all(),
            );
        }

        if ($validator->fails()) {
            $fail($validator->errors()->first());
        }
    }

    /**
     * Create new instance of the rule.
     */
    public static function make(): static
    {
        return new static(
            func_num_args() === 1 && is_array(func_get_args()[0])
                ? func_get_args()[0]
                : func_get_args()
        );
    }
}
