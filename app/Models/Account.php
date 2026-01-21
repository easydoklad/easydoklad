<?php

namespace App\Models;

use App\Enums\DocumentType;
use App\Enums\PaymentMethod;
use Brick\Money\Currency;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int $id
 * @property \App\Models\Company $company
 * @property bool $vat_enabled
 * @property string $invoice_numbering_format
 * @property string $invoice_variable_symbol_format
 * @property float $default_vat_rate
 * @property int $invoice_due_days
 * @property \App\Enums\PaymentMethod $invoice_payment_method
 * @property string|null $invoice_footer_note
 * @property string $invoice_template
 * @property \App\Models\DocumentTemplate $invoiceTemplate
 * @property \App\Models\Upload|null $invoiceSignature
 * @property \App\Models\Upload|null $invoiceLogo
 * @property int $next_invoice_number
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\NumberSequence> $numberSequences
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Invoice> $invoices
 * @property string|null $invoice_mail_message
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\BankTransactionAccount> $bankTransactionAccounts
 * @property \Illuminate\Database\Eloquent\Collection<int, \App\Models\Webhook> $webhooks
 */
class Account extends Model
{
    /** @use HasFactory<\Database\Factories\AccountFactory> */
    use HasApiTokens, HasFactory;

    protected $guarded = false;

    protected function casts(): array
    {
        return [
            'invoice_payment_method' => PaymentMethod::class,
            'vat_enabled' => 'boolean',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['role']);
    }

    public function invoiceSignature(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function invoiceLogo(): BelongsTo
    {
        return $this->belongsTo(Upload::class);
    }

    public function numberSequences(): HasMany
    {
        return $this->hasMany(NumberSequence::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function invoiceTemplate(): BelongsTo
    {
        return $this->belongsTo(DocumentTemplate::class);
    }

    public function bankTransactionAccounts(): HasMany
    {
        return $this->hasMany(BankTransactionAccount::class);
    }

    public function bankTransactions(): HasMany
    {
        return $this->hasMany(BankTransaction::class);
    }

    public function webhooks(): HasMany
    {
        return $this->hasMany(Webhook::class);
    }

    /**
     * Get the account currency.
     */
    public function getCurrency(): Currency
    {
        return Currency::of('EUR');
    }

    /**
     * Get the locale used for formatting monies.
     */
    public function getMoneyFormattingLocale(): string
    {
        return 'sk';
    }

    /**
     * Get the preferred document locale.
     */
    public function getPreferredDocumentLocale(): string
    {
        return 'sk';
    }

    /**
     * Make a new instance with default settings.
     */
    public static function makeWithDefaults(): static
    {
        $account = new static([
            'invoice_due_days' => 14,
            'invoice_numbering_format' => 'RRRRMMCCCC',
            'invoice_variable_symbol_format' => 'RRRRMMCCCC',
            'invoice_payment_method' => PaymentMethod::BankTransfer,
            'invoice_mail_message' => <<<'MESSAGE'
# Vážený klient,

v prílohe Vám zasielame elektronickú faktúru.
MESSAGE
        ]);

        $invoiceTemplate = DocumentTemplate::query()
            ->where('document_type', DocumentType::Invoice)
            ->where('is_default', true)
            ->first();

        if (! $invoiceTemplate) {
            throw new RuntimeException("Invalid state: The default invoice template is not set up");
        }

        $account->invoiceTemplate()->associate($invoiceTemplate);

        return $account;
    }
}
