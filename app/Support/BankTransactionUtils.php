<?php


namespace App\Support;


use Illuminate\Support\Str;

class BankTransactionUtils
{
    /**
     * Parses reference symbols. Returns a tripple with variable - specific - constant symbols.
     */
    public static function parseReferenceSymbols(?string $reference): array
    {
        if (is_null($reference)) {
            return [null, null, null];
        }

        $parseSymbolReference = function ($reference, $symbol) {
            foreach (explode('/', trim($reference, '/')) as $part) {
                if (str_starts_with($part, $symbol)) {
                    $value = Str::replaceFirst($symbol, '', trim($part));

                    if (strlen($value) > 0) {
                        return $value;
                    }

                    return null;
                }
            }

            return null;
        };

        return [
            $parseSymbolReference($reference, 'VS'),
            $parseSymbolReference($reference, 'SS'),
            $parseSymbolReference($reference, 'KS'),
        ];
    }
}
