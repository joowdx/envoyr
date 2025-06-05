<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/admin/dashboard', function () {
    return view('admin.dashboard');
})->name('filament.admin.pages.dashboard');

Route::get('/user/dashboard', function () {
    return view('user.dashboard');
})->name('filament.user.pages.dashboard');
