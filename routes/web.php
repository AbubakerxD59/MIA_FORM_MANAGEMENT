<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;
use App\Http\Controllers\FormController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\ProfileController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('forms.index');
    }
    return redirect()->route('login');
});

Route::get('/dashboard', function () {
    return redirect()->route('forms.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ============================================
    // Form Routes
    // ============================================
    Route::resource('forms', FormController::class);

    // Form Deleted Routes
    Route::get('forms-deleted', [FormController::class, 'deleted'])->name('forms.deleted');
    Route::post('forms/{id}/restore', [FormController::class, 'restore'])->name('forms.restore');

    // Form Duplicate Route
    Route::post('forms/duplicate', [FormController::class, 'duplicate'])->name('forms.duplicate');

    // Form Duplicate Group Route
    Route::post('forms/duplicate-group', [FormController::class, 'duplicateGroup'])->name('forms.duplicate-group');

    // Form Update Details Route
    Route::post('forms/update-details', [FormController::class, 'updateDetails'])->name('forms.update-details');

    // Form API Routes
    Route::get('api/client-names', [FormController::class, 'getClientNames'])->name('api.client-names');
    Route::get('api/group-by-values', [FormController::class, 'getGroupByValues'])->name('api.group-by-values');
    Route::get('api/forms/{form}/fields', [FormController::class, 'getFormFields'])->name('api.form-fields');
    Route::get('api/forms/sidebar-items', [FormController::class, 'getSidebarItems'])->name('api.sidebar-items');

    // Form Project Routes
    Route::get('forms/project/edit', [FormController::class, 'edit'])->name('forms.edit-by-project');
    Route::get('forms/project/export', [FormController::class, 'exportByProject'])->name('forms.export-by-project');
    Route::delete('forms/project/delete', [FormController::class, 'destroyByProject'])->name('forms.delete-by-project');

    // Form Export Routes
    Route::get('forms/{form}/export', [FormController::class, 'export'])->name('forms.export');
    Route::get('forms/project/export-group', [FormController::class, 'exportByGroup'])->name('forms.export-by-group');

    // ============================================
    // BBS (Bar Bending Schedule) Routes
    // ============================================
    Route::get('forms/bbs/{form}', [FormController::class, 'bbs'])->name('forms.bbs');

    // ============================================
    // Credit/Debit Routes
    // ============================================
    Route::get('forms/cd/{form}', [FormController::class, 'cd'])->name('forms.cd');

    // CD API Routes
    Route::post('api/forms/{form}/cd-heads', [FormController::class, 'storeCdHead'])->name('api.store-cd-head');
    Route::get('api/cd-heads/{head}', [FormController::class, 'getCdHead'])->name('api.get-cd-head');
    Route::put('api/cd-heads/{head}', [FormController::class, 'updateCdHead'])->name('api.update-cd-head');
    Route::post('api/cd-heads/{head}/items', [FormController::class, 'storeCdItems'])->name('api.store-cd-items');
    Route::post('api/forms/{form}/cd-ledger', [FormController::class, 'updateCdLedger'])->name('api.update-cd-ledger');
    Route::get('api/forms/{form}/cd-heads/autocomplete', [FormController::class, 'getCdHeadsForAutocomplete'])->name('api.cd-heads-autocomplete');
    Route::post('api/forms/{form}/cd-summary', [FormController::class, 'storeCdSummary'])->name('api.store-cd-summary');
    Route::get('api/forms/{form}/cd-heads', [FormController::class, 'getCdHeads'])->name('api.get-cd-heads');

    // BBS API Routes
    Route::get('api/forms/{form}/bar-bending-items', [FormController::class, 'getBarBendingFormItems'])->name('api.bar-bending-form-items');
    Route::get('api/bar-bending-form-items/{item}', [FormController::class, 'getBarBendingFormItem'])->name('api.get-bar-bending-form-item');
    Route::post('api/bar-bending-form-items', [FormController::class, 'storeBarBendingFormItem'])->name('api.store-bar-bending-form-item');
    Route::post('api/bar-bending-form-items/update-name', [FormController::class, 'updateBarBendingFormItemName'])->name('api.update-bar-bending-form-item-name');
    Route::delete('api/bar-bending-form-items/{item}', [FormController::class, 'deleteBarBendingFormItem'])->name('api.delete-bar-bending-form-item');
    Route::get('api/locations', [FormController::class, 'getLocations'])->name('api.locations');
    Route::post('api/bar-bending-form-locations', [FormController::class, 'addLocation'])->name('api.add-location');
    Route::delete('api/bar-bending-form-locations/{location}', [FormController::class, 'deleteLocation'])->name('api.delete-location');
    Route::get('api/formulas', [FormController::class, 'getFormulas'])->name('api.formulas');
    Route::post('api/formulas', [FormController::class, 'storeFormula'])->name('api.store-formula');
    Route::put('api/formulas/{formula}', [FormController::class, 'updateFormula'])->name('api.update-formula');
    Route::delete('api/formulas/{formula}', [FormController::class, 'deleteFormula'])->name('api.delete-formula');

    // ============================================
    // Invoice Routes
    // ============================================
    Route::resource('invoices', InvoiceController::class);
    Route::get('invoices/{invoice}/export', [InvoiceController::class, 'export'])->name('invoices.export');

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

Route::get('/clear', function () {
    Artisan::call('config:clear');
    Artisan::call('view:clear');
    return "Cache cleared!";
});

require __DIR__ . '/auth.php';
