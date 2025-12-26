<?php

use App\Http\Controllers\FormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('forms.index');
});

Route::resource('forms', FormController::class);
Route::get('forms-deleted', [FormController::class, 'deleted'])->name('forms.deleted');
Route::post('forms/{id}/restore', [FormController::class, 'restore'])->name('forms.restore');
Route::get('api/client-names', [FormController::class, 'getClientNames'])->name('api.client-names');
Route::get('api/forms/{form}/fields', [FormController::class, 'getFormFields'])->name('api.form-fields');
Route::get('api/forms/sidebar-items', [FormController::class, 'getSidebarItems'])->name('api.sidebar-items');
Route::get('forms/project/edit', [FormController::class, 'edit'])->name('forms.edit-by-project');
Route::get('forms/project/export', [FormController::class, 'exportByProject'])->name('forms.export-by-project');
Route::delete('forms/project/delete', [FormController::class, 'destroyByProject'])->name('forms.delete-by-project');
Route::get('forms/{form}/export', [FormController::class, 'export'])->name('forms.export');
