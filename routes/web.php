<?php

use App\Http\Controllers\DocumentDownloadController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('documents/{document}/download', DocumentDownloadController::class)
    ->name('document.download')
    ->middleware(['auth']);
