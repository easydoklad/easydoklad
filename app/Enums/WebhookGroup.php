<?php

namespace App\Enums;

use StackTrace\Ui\Contracts\HasLabel;

enum WebhookGroup: string implements HasLabel
{
    case Invoices = 'invoices';

    public function label(): string
    {
        return match ($this) {
            self::Invoices => 'Faktúry',
        };
    }
}
