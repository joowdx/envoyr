<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;

// User invitation/registration routes with signed URLs
Route::get('/register/{user}', [RegistrationController::class, 'show'])
    ->name('register.show')
    ->middleware(['guest', 'signed']); // Add signed middleware

Route::post('/register/{user}', [RegistrationController::class, 'store'])
    ->name('register.store')
    ->middleware(['guest', 'signed']); // Add signed middleware 
