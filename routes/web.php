<?php

use App\Http\Controllers\FormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('forms.index');
});

// ============================================
// Form Routes
// ============================================
Route::resource('forms', FormController::class);

// Form Deleted Routes
Route::get('forms-deleted', [FormController::class, 'deleted'])->name('forms.deleted');
Route::post('forms/{id}/restore', [FormController::class, 'restore'])->name('forms.restore');

// Form Duplicate Route
Route::post('forms/duplicate', [FormController::class, 'duplicate'])->name('forms.duplicate');

// Form Update Details Route
Route::post('forms/update-details', [FormController::class, 'updateDetails'])->name('forms.update-details');

// Form API Routes
Route::get('api/client-names', [FormController::class, 'getClientNames'])->name('api.client-names');
Route::get('api/forms/{form}/fields', [FormController::class, 'getFormFields'])->name('api.form-fields');
Route::get('api/forms/sidebar-items', [FormController::class, 'getSidebarItems'])->name('api.sidebar-items');

// Form Project Routes
Route::get('forms/project/edit', [FormController::class, 'edit'])->name('forms.edit-by-project');
Route::get('forms/project/export', [FormController::class, 'exportByProject'])->name('forms.export-by-project');
Route::delete('forms/project/delete', [FormController::class, 'destroyByProject'])->name('forms.delete-by-project');

// Form Export Routes
Route::get('forms/{form}/export', [FormController::class, 'export'])->name('forms.export');

// ============================================
// BBS (Bar Bending Schedule) Routes
// ============================================
Route::get('forms/bbs/{form}', [FormController::class, 'bbs'])->name('forms.bbs');

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
