<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| FIDA routes
|--------------------------------------------------------------------------
|
| Only the user with email hfia6232@gmail.com may access these routes
| (see App\Http\Middleware\FidaUser).
|
*/

Route::middleware(['auth', 'fida.user'])->group(function () {
    // Example: Route::get('/fida', ...)->name('fida.index');
});
