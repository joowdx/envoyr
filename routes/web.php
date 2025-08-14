<?php

use Illuminate\Support\Facades\Route;

// Route::get('/', fn () => view('welcome'));
Route::middleware(['auth'])->group(function () {
    Route::get('/force-password-reset', [App\Http\Controllers\ForcedPasswordResetController::class, 'show'])
        ->middleware('force.password.reset')
        ->name('password.reset.force');
    Route::post('/force-password-reset', [App\Http\Controllers\ForcedPasswordResetController::class, 'update']);
});
