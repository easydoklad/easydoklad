<?php

namespace App\Models;

use App\Enums\Country;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string|null $line_one
 * @property string|null $line_two
 * @property string|null $line_three
 * @property string|null $postal_code
 * @property string|null $city
 * @property Country|null $country
 */
class Address extends Model
{
    /** @use HasFactory<\Database\Factories\AddressFactory> */
    use HasFactory;

    protected $guarded = false;

    protected function casts(): array
    {
        return [
            'country' => Country::class,
        ];
    }

    public function asMultipleLines(): ?array
    {
        $segments = collect([
            $this->line_one,
            $this->line_two,
            $this->line_three,
        ]);

        if ($this->postal_code && $this->city) {
            $segments->push("{$this->postal_code} {$this->city}");
        } else if ($this->postal_code) {
            $segments->push($this->postal_code);
        } else if ($this->city) {
            $segments->push($this->city);
        }

        $segments->push($this->country?->label());

        $segments = $segments->filter(fn (?string $segment) => is_string($segment) && strlen($segment) > 0)->values();

        return $segments->isNotEmpty() ? $segments->all() : null;
    }

    public function asSingleLine(string $glue = ', '): ?string
    {
        if ($lines = $this->asMultipleLines()) {
            return implode($glue, $lines);
        }

        return null;
    }
}
