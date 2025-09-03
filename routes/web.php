<?php

use App\Http\Controllers\RegistrationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/register/{user}', [RegistrationController::class, 'show'])
    ->name('register.show')
    ->middleware(['guest', 'signed']); // Add signed middleware

Route::post('/register/{user}', [RegistrationController::class, 'store'])
    ->name('register.store')
    ->middleware(['guest', 'signed']); // Add signed middleware

Route::get('/complete-profile', [ProfileController::class, 'showCompleteForm'])
    ->middleware(\Filament\Http\Middleware\Authenticate::class); 
Route::post('/complete-profile', [ProfileController::class, 'storeCompleteForm'])
    ->middleware(\Filament\Http\Middleware\Authenticate::class);

