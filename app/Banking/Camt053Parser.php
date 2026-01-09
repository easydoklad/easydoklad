<?php


namespace App\Banking;


use App\Enums\BankTransactionSource;
use App\Enums\BankTransactionType;
use App\Support\BankTransactionUtils;
use Brick\Money\Money;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use SimpleXMLElement;

class Camt053Parser
{
    public function __construct(
        protected string $content
    ) { }

    protected function processEntry(string $receivedToIban, SimpleXMLElement $entry): ?PendingTransaction
    {
        if ((string) $entry->CdtDbtInd === 'DBIT') {
            return null;
        }

        $date = Carbon::parse((string) $entry->NtryDtls->TxDtls->RltdDts->TxDtTm);
        $amount = Money::of((string) $entry->Amt, (string) $entry->Amt['Ccy']);

        $sentFromName = null;
        if ($senderNameEl = $entry->NtryDtls->TxDtls->RltdPties->Dbtr->Nm) {
            $sentFromName = (string) $senderNameEl;
        }

        $sentFromIban = null;

        if ($sentFromIbanEl = $entry->NtryDtls->TxDtls->RltdPties->DbtrAcct->Id) {
            $sentFromIban = (string) $sentFromIbanEl->IBAN;;
        }

        if (! $sentFromIban) {
            return null;
        }

        $description = null;

        if ($descriptionEl = $entry->NtryDtls->TxDtls->RmtInf->Ustrd) {
            $description = (string) $descriptionEl;
        }

        $reference = null;
        if ($referenceEl = $entry->NtryDtls->TxDtls->Refs->EndToEndId) {
            $reference = (string) $referenceEl;
        }

        [$variable, $specific, $constant] = BankTransactionUtils::parseReferenceSymbols($reference);

        return new PendingTransaction(
            source: BankTransactionSource::Camt053,
            type: BankTransactionType::Credit,
            date: $date,
            sentFromName: $sentFromName,
            sentFromIban: $sentFromIban,
            receivedToIban: $receivedToIban,
            amount: $amount,
            variableSymbol: $variable,
            specificSymbol: $specific,
            constantSymbol: $constant,
            description: $description,
            reference: $reference
        );
    }

    /**
     * Retrieve list of credit transactions.
     *
     * @return \Illuminate\Support\Collection<int, \App\Banking\PendingTransaction>
     */
    public function getTransactions(): Collection
    {
        $xml = simplexml_load_string($this->content);

        $xml->registerXPathNamespace('camt', 'urn:iso:std:iso:20022:tech:xsd:camt.053.001.02');

        $receivedToIban = (string) $xml->xpath('//camt:BkToCstmrStmt/camt:Stmt/camt:Acct/camt:Id/camt:IBAN')[0];

        return collect($xml->xpath('//camt:BkToCstmrStmt/camt:Stmt/camt:Ntry'))
            ->map(fn (SimpleXMLElement $entry) => $this->processEntry($receivedToIban, $entry))
            ->filter()
            ->values();
    }
}
