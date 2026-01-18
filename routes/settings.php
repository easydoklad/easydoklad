<?php

use App\Http\Controllers\Settings\AccountController;
use App\Http\Controllers\Settings\ApiKeyController;
use App\Http\Controllers\Settings\BankAccountController;
use App\Http\Controllers\Settings\BankTransactionAccountController;
use App\Http\Controllers\Settings\BankTransactionsController;
use App\Http\Controllers\Settings\ChangeInvoiceLogoController;
use App\Http\Controllers\Settings\ChangeInvoiceSignatureController;
use App\Http\Controllers\Settings\InvoiceController;
use App\Http\Controllers\Settings\PasswordController;
use App\Http\Controllers\Settings\ProfileController;
use App\Http\Controllers\Settings\ToggleWebhookController;
use App\Http\Controllers\Settings\WebhookController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::middleware('auth')->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::get('settings/password', [PasswordController::class, 'edit'])->name('password.edit');
    Route::put('settings/password', [PasswordController::class, 'update'])->name('password.update');

    Route::get('settings/appearance', function () {
        return Inertia::render('Settings/Appearance');
    })->name('appearance');

    Route::get('settings/account', [AccountController::class, 'edit'])->name('accounts.edit');
    Route::patch('settings/account', [AccountController::class, 'update'])->name('accounts.update');
    Route::patch('settings/bank-account', [BankAccountController::class, 'update'])->name('bank-accounts.update');

    Route::get('settings/invoices', [InvoiceController::class, 'edit'])->name('settings.invoices.edit');
    Route::patch('settings/invoices', [InvoiceController::class, 'update'])->name('invoices.settings.update');
    Route::patch('settings/invoices/signature', ChangeInvoiceSignatureController::class)->name('invoices.settings.signature');
    Route::patch('settings/invoices/logo', ChangeInvoiceLogoController::class)->name('invoices.settings.logo');

    Route::get('settings/bank-transactions', BankTransactionsController::class)->name('settings.bank-transactions');
    Route::post('settings/bank-transaction-accounts', [BankTransactionAccountController::class, 'store'])->name('bank-transaction-accounts.store');
    Route::delete('settings/bank-transaction-accounts/{account:uuid}', [BankTransactionAccountController::class, 'destroy'])->name('bank-transaction-accounts.destroy');

    Route::get('settings/api-keys', [ApiKeyController::class, 'index'])->name('settings.api-keys');
    Route::post('settings/api-keys', [ApiKeyController::class, 'store'])->name('api-keys.store');
    Route::delete('settings/api-keys/{token}', [ApiKeyController::class, 'destroy'])->name('api-keys.destroy');

    Route::get('settings/webhooks', [WebhookController::class, 'index'])->name('webhooks');
    Route::post('settings/webhooks', [WebhookController::class, 'store'])->name('webhooks.store');
    Route::patch('settings/webhooks/{webhook:uuid}', [WebhookController::class, 'update'])->name('webhooks.update');
    Route::delete('settings/webhooks/{webhook:uuid}', [WebhookController::class, 'destroy'])->name('webhooks.destroy');
    Route::post('settings/webhooks/{webhook:uuid}/toggle-active', ToggleWebhookController::class)->name('webhooks.toggle-active');
});
