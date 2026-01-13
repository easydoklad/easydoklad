<?php

namespace App\Banking\MailParsers;

use App\Banking\MailParser;
use App\Banking\PendingTransaction;
use App\Enums\BankTransactionSource;
use App\Enums\BankTransactionType;
use App\Support\IbanGenerator;
use BeyondCode\Mailbox\InboundEmail;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Str;
use Throwable;

class TatraBankaBMailParser implements MailParser
{
    public function __construct(
        protected IbanGenerator $ibanGenerator
    ) {}

    public function parse(InboundEmail $email): ?PendingTransaction
    {
        $rawText = trim($email->text());

        $type = $this->resolveType($rawText);

        if (! $type) {
            return null;
        }

        $receivedToIban = $this->parseReceivedToIban($rawText);

        if (! $receivedToIban) {
            return null;
        }

        $description = $this->parseDescription($rawText);

        if (! $description) {
            return null;
        }

        $sentFromIban = $this->parseSentFromIban($description);

        if (! $sentFromIban) {
            return null;
        }

        $amount = $this->parseAmount($rawText);

        if (! $amount) {
            return null;
        }

        $reference = $this->parseReference($rawText);

        $date = $this->parseDate($rawText);

        if (! $date) {
            return null;
        }

        return new PendingTransaction(
            source: BankTransactionSource::MailNotification,
            type: $type,
            date: $date,
            sentFromName: $this->parseReceivedFromAccountName($rawText),
            sentFromIban: $sentFromIban,
            receivedToIban: $receivedToIban,
            amount: $amount,
            variableSymbol: $reference ? $this->parseSymbolFromReference($reference, 'VS') : null,
            specificSymbol: $reference ? $this->parseSymbolFromReference($reference, 'SS') : null,
            constantSymbol: $reference ? $this->parseSymbolFromReference($reference, 'KS') : null,
            description: $description,
            reference: $reference,
        );
    }

    protected function resolveType(string $source): ?BankTransactionType
    {
        if (Str::contains($source, 'zvyseny')) {
            return BankTransactionType::Credit;
        }

        return null;
    }

    protected function parseSymbolFromReference(string $reference, string $symbol): ?string
    {
        foreach (explode('/', trim($reference, '/')) as $part) {
            if (str_starts_with($part, $symbol)) {
                $value = Str::replaceFirst($symbol, '', trim($part));

                if (strlen($value) > 0) {
                    return ltrim($value, '0');
                }

                return null;
            }
        }

        return null;
    }

    protected function parseReference(string $source): ?string
    {
        $re = '/^Referencia\splatitela:\s(.*)$/m';

        preg_match_all($re, $source, $matches, PREG_SET_ORDER);

        if (count($matches) > 0 && count($matches[0]) == 2) {
            $reference = trim($matches[0][1]);

            if (strlen($reference) >= 1) {
                return $reference;
            }
        }

        return null;
    }

    protected function parseAmount(string $source): ?Money
    {
        preg_match_all('/zvyseny\so\s([\d,\s]+)\s([A-Z]{3})/m', $source, $matches);

        if (count($matches) === 3 && count($matches[1]) === 1 && count($matches[2]) === 1) {
            $amount = Str::of($matches[1][0])->replace([' ', '.'], '')->replace(',', '.')->value();
            $currency = $matches[2][0];

            try {
                return Money::of($amount, $currency);
            } catch (Throwable) {
                //
            }
        }

        return null;
    }

    protected function parseSentFromIban(string $description): ?string
    {
        // TODO: SK verzia ibanu iba funguje
        $re = '/(platba|tpp)\s(\d+)\/(\d+)-(\d+)/mi';

        preg_match_all($re, strtolower($description), $matches, PREG_SET_ORDER);

        if (count($matches) == 1 && count($matches[0]) == 5) {
            $bankPrefix = $matches[0][2];
            $accountPrefix = $matches[0][3];
            $accountNumber = $matches[0][4];

            try {
                return $this->ibanGenerator->generate($bankPrefix, "{$accountPrefix}{$accountNumber}", 'SK');
            } catch (Throwable) {
                //
            }
        }

        return null;
    }

    protected function parseDescription(string $source): ?string
    {
        $re = '/^Popis\stransakcie:\s(.*)$/m';

        preg_match_all($re, $source, $matches, PREG_SET_ORDER);

        if (count($matches) > 0 && count($matches[0]) == 2) {
            $description = trim($matches[0][1]);

            if (strlen($description) >= 1) {
                return $description;
            }
        }

        return null;
    }

    protected function parseReceivedFromAccountName(string $source): ?string
    {
        $re = '/^Ucet\sprotistrany:\s(.*)$/m';

        preg_match_all($re, $source, $matches, PREG_SET_ORDER);

        if (count($matches) > 0 && count($matches[0]) == 2) {
            $name = trim($matches[0][1]);

            if (strlen($name) >= 1) {
                return $name;
            }
        }

        return null;
    }

    protected function parseReceivedToIban(string $source): ?string
    {
        $re = '/^.+uctu\s(.*)\szvyseny.*$/m';

        preg_match_all($re, $source, $matches, PREG_SET_ORDER);

        if (count($matches) > 0 && count($matches[0]) == 2) {
            return $matches[0][1];
        }

        return null;
    }

    protected function parseDate(string $source): ?Carbon
    {
        $re = '/^(\d\d?.\d\d?.\d\d\d\d\s\d\d?:\d\d)\sbol\szostatok.*$/m';

        preg_match_all($re, $source, $matches, PREG_SET_ORDER);

        if (count($matches) > 0) {
            if (count($matches[0]) == 2) {
                $rawDate = $matches[0][1];

                if (is_string($rawDate)) {
                    try {
                        return Carbon::createFromFormat('d.m.Y H:i', $rawDate);
                    } catch (Throwable) {
                        //
                    }
                }
            }
        }

        return null;
    }
}
