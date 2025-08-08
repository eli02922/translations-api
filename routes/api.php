<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TranslationsController;

Route::prefix('translations')->group(function () {
    Route::get('/', [TranslationsController::class, 'index']);
    Route::get('/export', [TranslationsController::class, 'export']);
    Route::post('/', [TranslationsController::class, 'store']);
    Route::put('/update/{id}', [TranslationsController::class, 'update']);
    Route::delete('/delete/{id}', [TranslationsController::class, 'destroy']);
});