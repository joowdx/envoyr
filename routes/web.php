<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RegistrationController;


// User invitation/registration routes
Route::get('/register/{token}', [RegistrationController::class, 'show'])
    ->name('register.show')
    ->middleware('guest'); 

Route::post('/register/{token}', [RegistrationController::class, 'store'])
    ->name('register.store')
    ->middleware('guest'); 
