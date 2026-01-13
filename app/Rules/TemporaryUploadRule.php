<?php

namespace App\Rules;

use App\Facades\Accounts;
use App\Models\TemporaryUpload;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class TemporaryUploadRule implements ValidationRule
{
    public function __construct(
        protected string $scope
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (is_null($value)) {
            return;
        }

        if (is_string($value)) {
            if (
                TemporaryUpload::query()
                    ->where('uuid', $value)
                    ->where('scope', $this->scope)
                    ->whereBelongsTo(Accounts::current())
                    ->exists()
            ) {
                return;
            }
        }

        $fail('Tento súbor neexistuje. Skúste ho nahrať znovu.');
    }

    /**
     * Create a new rule instance.
     */
    public static function scope(string $scope): static
    {
        return new static($scope);
    }
}
