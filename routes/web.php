<?php

use App\Http\Controllers\FormController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('forms.index');
});

Route::resource('forms', FormController::class);
Route::get('forms/{form}/export', [FormController::class, 'export'])->name('forms.export');
Route::get('forms-deleted', [FormController::class, 'deleted'])->name('forms.deleted');
Route::post('forms/{id}/restore', [FormController::class, 'restore'])->name('forms.restore');
