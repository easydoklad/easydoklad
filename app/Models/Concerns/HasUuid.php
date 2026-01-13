<?php

namespace App\Models\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * @property string $uuid
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasUuid
{
    public static function bootHasUuid(): void
    {
        static::creating(function (Model $model) {
            if (empty($model->uuid)) {
                $model->uuid = Str::uuid()->toString();
            }
        });
    }

    /**
     * Find model by UUID.
     */
    public static function findByUUID(string $uuid): ?static
    {
        return static::query()->firstWhere('uuid', $uuid);
    }

    /**
     * Get the model by UUID or throw exception where it does not exist.
     */
    public static function findOrFailByUUID(string $uuid): static
    {
        return static::query()->where('uuid', $uuid)->firstOrFail();
    }
}
