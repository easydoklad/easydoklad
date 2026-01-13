<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API as Controllers;

Route::prefix('v1')->middleware('auth:api')->group(function () {
    Route::get('invoices', [Controllers\InvoiceController::class, 'index']);
    Route::post('invoices', [Controllers\InvoiceController::class, 'store']);
    Route::get('invoices/{invoice:uuid}', [Controllers\InvoiceController::class, 'show']);
    Route::patch('invoices/{invoice:uuid}', [Controllers\InvoiceController::class, 'update']);
    Route::get('invoices/{invoice:uuid}/signature', [Controllers\InvoiceSignatureController::class, 'show']);
    Route::get('invoices/{invoice:uuid}/logo', [Controllers\InvoiceLogoController::class, 'show']);
    // TODO: GET invoices/{invoice:uuid}/pay-by-square
});
