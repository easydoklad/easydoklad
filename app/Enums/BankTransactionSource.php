<?php


namespace App\Enums;


enum BankTransactionSource: string
{
    case MailNotification = 'mail';
    case Camt053 = 'camt053';
}
