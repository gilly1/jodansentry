<?php

use App\Http\Controllers\Mpesa\MpesaResultController;
use App\Http\Controllers\Mpesa\MpesaTimeoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/mpesa/b2c/result', [MpesaResultController::class, 'store'])
    ->name('mpesa.b2c.result');

Route::post('/mpesa/b2c/timeout', [MpesaTimeoutController::class, 'store'])
    ->name('mpesa.b2c.timeout');
