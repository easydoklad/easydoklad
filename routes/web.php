<?php

use App\Http\Controllers\AcceptInvitationController;
use App\Http\Controllers\AccountController;
use App\Http\Controllers\Banking\BankTransactionController;
use App\Http\Controllers\Banking\CamtImportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Invoice\DownloadInvoiceController;
use App\Http\Controllers\Invoice\DuplicateController;
use App\Http\Controllers\Invoice\InvoiceController;
use App\Http\Controllers\Invoice\InvoiceLockController;
use App\Http\Controllers\Invoice\IssueInvoiceController;
use App\Http\Controllers\Invoice\PaymentController;
use App\Http\Controllers\Invoice\SendController;
use App\Http\Controllers\Invoice\SentFlagController;
use App\Http\Controllers\Invoice\SerializeInvoiceController;
use App\Http\Controllers\SwitchAccountController;
use App\Http\Controllers\TemporaryUploadController;
use App\Http\Middleware\AccountSelectedMiddleware;
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

Route::get('test', function () {
    $invoice = \App\Models\Invoice::findOrFailByUUID('3a5dbabb-70da-4731-875a-f2a4f037e660');

    $mail = \App\Mail\InvoiceMail::make($invoice);

    \Illuminate\Support\Facades\Mail::to('peter@peterstovka.com')->send($mail);

    return $mail;
});

Route::get('/', HomeController::class)->name('home');

Route::get('/invitation/{invitation}', [AcceptInvitationController::class, 'create'])->name('accept-invitation');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::post('/invitation/{invitation}', [AcceptInvitationController::class, 'store'])->name('accept-invitation.store');

    Route::get('create-account', [AccountController::class, 'create'])->name('accounts.create');
    Route::post('accounts', [AccountController::class, 'store'])->name('accounts.store');

    Route::middleware(AccountSelectedMiddleware::class)->group(function () {
        Route::get('dashboard', DashboardController::class)->name('dashboard');

        Route::post('/switch-account/{account}', SwitchAccountController::class)->name('accounts.switch');

        Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices');
        Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('/invoices/{invoice:uuid}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::patch('/invoices/{invoice:uuid}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('/invoices/{invoice:uuid}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
        Route::get('/invoices/{invoice:uuid}/json', SerializeInvoiceController::class)->name('invoices.serialize');
        Route::post('/invoices/{invoice:uuid}/issue', IssueInvoiceController::class)->name('invoices.issue');
        Route::post('/invoices/{invoice:uuid}/lock', [InvoiceLockController::class, 'store'])->name('invoices.lock.store');
        Route::delete('/invoices/{invoice:uuid}/lock', [InvoiceLockController::class, 'destroy'])->name('invoices.lock.destroy');
        Route::get('/invoices/{invoice:uuid}/download', DownloadInvoiceController::class)->name('invoices.download');
        Route::post('/invoices/{invoice:uuid}/flags/sent', [SentFlagController::class, 'store'])->name('invoices.sent-flag.store');
        Route::delete('/invoices/{invoice:uuid}/flags/sent', [SentFlagController::class, 'destroy'])->name('invoices.sent-flag.destroy');
        Route::post('/invoices/{invoice:uuid}/send', SendController::class)->name('invoices.send')->middleware('throttle:mail');
        Route::post('/invoices/{invoice:uuid}/duplicate', DuplicateController::class)->name('invoices.duplicate');
        Route::post('/invoices/{invoice:uuid}/payments', [PaymentController::class, 'store'])->name('invoices.payments.store');

        Route::get('/bank-transactions', [BankTransactionController::class, 'index'])->name('bank-transactions');
        Route::post('/bank-transactions/camt-import', CamtImportController::class)->name('bank-transactions.camt-import');

        Route::post('/files', [TemporaryUploadController::class, 'store'])->name('files.store');

        Route::prefix('/expenses')
            ->group(function () {
                Route::get('/', [ExpenseController::class, 'index'])->name('expenses');
            })
            ->middleware(EnsureFeaturesAreActive::using('expenses'));
    });
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
