<?php

namespace App\Support;

use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Arr;

class Patch
{
    public function __construct(
        protected array $input = []
    ) {}

    /**
     * Update field only if it is present in the input.
     */
    public function present(string $name, Closure $closure): static
    {
        if (Arr::has($this->input, $name)) {
            call_user_func($closure, Arr::get($this->input, $name));
        }

        return $this;
    }

    /**
     * Update all attributes present in the input.
     */
    public function all(Model $model, array $map = []): static
    {
        $attributes = collect($this->input)
            ->mapWithKeys(fn ($value, $key) => [Arr::has($map, $key) ? Arr::get($map, $key) : $key => $value])
            ->all();

        if (! empty($attributes)) {
            $model->update($attributes);
        }

        return $this;
    }

    /**
     * Update only selected fields present in the input.
     */
    public function fillOnly(Model $model, array $fields): static
    {
        $attributes = collect($fields)
            ->mapWithKeys(function ($value, $key) {
                if ($value instanceof Closure) {
                    if (Arr::has($this->input, $key)) {
                        $result = $value($this->input[$key]);

                        if (is_array($result) && Arr::isAssoc($result) && count(array_keys($result)) === 1) {
                            return $result;
                        }

                        return [$key => $result];
                    }
                } else {
                    $inputName = is_string($key) && is_string($value) ? $key : $value;

                    if (Arr::has($this->input, $inputName)) {
                        return [$value => $this->input[$inputName]];
                    }
                }

                return [];
            })
            ->all();

        if (! empty($attributes)) {
            $model->fill($attributes);
        }

        return $this;
    }

    /**
     * Get value from the input.
     */
    public function get(string $key, $default = null): mixed
    {
        return Arr::get($this->input, $key, $default);
    }
}
