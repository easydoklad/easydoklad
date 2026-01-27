<?php

namespace App\Enums;

use StackTrace\Ui\Contracts\HasLabel;

enum UserAccountRole: int implements HasLabel
{
    case Owner = 1;
    case User = 2;

    public function asString(): string
    {
        return match ($this) {
            self::Owner => 'owner',
            self::User => 'user',
        };
    }

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Majiteľ',
            self::User => 'Používateľ',
        };
    }

    public static function fromString(string $value): UserAccountRole
    {
        return match ($value) {
            'owner' => self::Owner,
            'user' => self::User,
        };
    }
}
