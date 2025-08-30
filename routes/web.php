<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

// User invitation/registration routes with signed URLs
Route::get('/register/{user}', [RegistrationController::class, 'show'])
    ->name('register.show')
    ->middleware(['guest', 'signed']); // Add signed middleware

Route::post('/register/{user}', [RegistrationController::class, 'store'])
    ->name('register.store')
    ->middleware(['guest', 'signed']); // Add signed middleware

Route::get('/complete-profile', [ProfileController::class, 'showCompleteForm'])
    ->name('complete-profile')
    ->middleware('auth'); 
Route::post('/complete-profile', [ProfileController::class, 'storeCompleteForm'])
    ->middleware('auth');

