<?php

namespace App\Support;

class IbanGenerator
{
    /**
     * Generate the IBAN from bank account number.
     */
    public function generate(string $bankCode, string $bankAccountNr, string $locale): string
    {
        $BBAN = $this->getBBAN($bankCode, $bankAccountNr);

        $checksum = $this->getChecksum($bankCode, $bankAccountNr, $locale);

        $checkcipher = $this->getCheckCipher($checksum);

        return $locale.$checkcipher.$BBAN;
    }

    public function getCheckCipher(string $checksum): string
    {
        return str_pad(98 - bcmod($checksum, 97), 2, '0', STR_PAD_LEFT);
    }

    public function getChecksum(string $bankCode, string $bankAccountNr, string $locale): string
    {
        return $this->getBBAN($bankCode, $bankAccountNr).$this->getNumericLanguageCode($locale);
    }

    public function getBBAN(string $bankCode, string $bankAccountNr): string
    {
        return $bankCode.str_pad($bankAccountNr, 10, '0', STR_PAD_LEFT);
    }

    public function getNumericLanguageCode(string $locale): string
    {
        $alphabet = [
            1 => 'A', 2 => 'B', 3 => 'C', 4 => 'D', 5 => 'E', 6 => 'F', 7 => 'G', 8 => 'H',
            9 => 'I', 10 => 'J', 11 => 'K', 12 => 'L', 13 => 'M', 14 => 'N', 15 => 'O',
            16 => 'P', 17 => 'Q', 18 => 'R', 19 => 'S', 20 => 'T', 21 => 'U', 22 => 'V',
            23 => 'W', 24 => 'X', 25 => 'Y', 26 => 'Z',
        ];

        $numericLanguageCode = '';

        foreach (str_split($locale) as $char) {
            $numericLanguageCode .= array_search($char, $alphabet) + 9;
        }

        return $numericLanguageCode.'00';
    }
}
