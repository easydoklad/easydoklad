<?php

namespace App\Enums;

enum BankTransactionType: string
{
    case Credit = 'credit';
    case Debit = 'debit';
}
