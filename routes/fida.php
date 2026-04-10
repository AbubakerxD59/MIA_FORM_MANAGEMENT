<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\InvoiceController;

/*
|--------------------------------------------------------------------------
| FIDA routes
|--------------------------------------------------------------------------
|
| Only the user with email hfida6232@gmail.com may access these routes
| (see App\Http\Middleware\FidaUser).
|
| Invoice routes mirror routes/web.php but under the `fida/` prefix and
| with route names prefixed by `fida.` (e.g. fida.invoices.index,
| fida.api.invoice-summaries).
|
*/

Route::middleware(['auth', 'fida.user'])->prefix('fida')->name('fida.')->group(function () {
    // ============================================
    // Invoice Routes (duplicate of web.php)
    // ============================================
    Route::get('invoices/{invoice}/export', [InvoiceController::class, 'exportFida'])->name('invoices.export');
    Route::get('invoices/{invoice}/quotation', [InvoiceController::class, 'quotationFida'])->name('invoices.quotation');
    Route::resource('invoices', InvoiceController::class);

    // Invoice API Routes
    Route::get('api/invoices/{invoice}/summaries', [InvoiceController::class, 'getInvoiceSummaries'])->name('api.invoice-summaries');
    Route::post('api/invoices/{invoice}/summaries', [InvoiceController::class, 'saveInvoiceSummaries'])->name('api.save-invoice-summaries');
    Route::post('api/invoices/create-item', [InvoiceController::class, 'createInvoiceItem'])->name('api.create-invoice-item');
    Route::put('api/invoices/items/{invoiceItem}', [InvoiceController::class, 'updateInvoiceItem'])->name('api.update-invoice-item');
    Route::delete('api/invoices/items/{invoiceItem}', [InvoiceController::class, 'deleteInvoiceItem'])->name('api.delete-invoice-item');
    Route::post('api/invoices/create-rate', [InvoiceController::class, 'createInvoiceRate'])->name('api.create-invoice-rate');
    Route::put('api/invoices/rates/{invoiceRate}', [InvoiceController::class, 'updateInvoiceRate'])->name('api.update-invoice-rate');
    Route::delete('api/invoices/rates/{invoiceRate}', [InvoiceController::class, 'deleteInvoiceRate'])->name('api.delete-invoice-rate');
    Route::get('api/invoice-rates', [InvoiceController::class, 'getInvoiceRates'])->name('api.invoice-rates');
});
