<?php

use App\Http\Controllers\RegistrationController;
use Illuminate\Support\Facades\Route;

// User invitation/registration routes with signed URLs
Route::get('/register/{user}', [RegistrationController::class, 'show'])
    ->name('register.show')
    ->middleware(['guest', 'signed']); // Add signed middleware

Route::post('/register/{user}', [RegistrationController::class, 'store'])
    ->name('register.store')
    ->middleware(['guest', 'signed']); // Add signed middleware
