<?php


namespace App\Enums;


enum BankTransactionAccountType: string
{
    case TatraBankBMail = 'tatra-banka-bmail';

    public function label(): string
    {
        return match ($this) {
            BankTransactionAccountType::TatraBankBMail => 'Tatra Banka B-Mail',
        };
    }

    public function worksThroughMailNotifications(): bool
    {
        return match ($this) {
            BankTransactionAccountType::TatraBankBMail => true,
        };
    }

    public function getConfigurationHelpLink(): ?string
    {
        return match ($this) {
            BankTransactionAccountType::TatraBankBMail => 'https://docs.easydoklad.sk/integracie/tatra-banka-bmail',
        };
    }
}
