<?php

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
use Illuminate\Support\Facades\Route;
use Laravel\Pennant\Middleware\EnsureFeaturesAreActive;

Route::get('/', HomeController::class)->name('home');

Route::get('test-mail', function () {
    $text = <<<EOF
Vazeny klient,

8.7.2022 14:38 bol zostatok Vasho uctu SK8511000000002917613377 zvyseny o 43,30 EUR.
uctovny zostatok:                                14,88 EUR
aktualny zostatok:                               14,88 EUR
disponibilny zostatok:                          714,88 EUR

Popis transakcie: Platba 1100/000000-2945102347
Referencia platitela: /VS106/SS202206/KS0558
Ucet protistrany: StackTrace s. r. o.

S pozdravom

TATRA BANKA, a.s.

http://www.tatrabanka.sk

Poznamka: Vase pripomienky alebo otazky tykajuce sa tejto spravy alebo inej nasej sluzby nam poslite, prosim, pouzitim kontaktneho formulara na nasej Web stranke.

Odporucame Vam mazat si po precitani prichadzajuce bmail notifikacie. Historiu uctu najdete v ucelenom tvare v pohyboch cez internet banking a nemusite ju pracne skladat zo starych bmailov.
EOF;

    \Illuminate\Support\Facades\Mail::raw($text, function (\Illuminate\Mail\Message $message) {
        $message->to('banka+abe637ce-691d-4dcc-94bb-a523aa5521a2@in.easydoklad.sk');
    });

    return 'ok';
});

Route::middleware(['auth', 'verified'])->group(function () {
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

    Route::post('/files', [TemporaryUploadController::class, 'store'])->name('files.store');

    Route::prefix('/expenses')
        ->group(function () {
            Route::get('/', [ExpenseController::class, 'index'])->name('expenses');
        })
        ->middleware(EnsureFeaturesAreActive::using('expenses'));
});

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
