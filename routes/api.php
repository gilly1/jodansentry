<?php

use App\Http\Controllers\Mpesa\MpesaResultController;
use App\Http\Controllers\Mpesa\MpesaTimeoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// M-Pesa Callbacks (no auth - called by Safaricom)
Route::prefix('sekani')->group(function () {
    Route::post('/b2c/result', [MpesaResultController::class, 'b2cResult']);
    Route::post('/b2c/timeout', [MpesaTimeoutController::class, 'b2cTimeout']);
    Route::post('/account-balance/result', [MpesaResultController::class, 'accountBalanceResult']);
    Route::post('/account-balance/timeout', [MpesaTimeoutController::class, 'accountBalanceTimeout']);
    Route::post('/transaction-status/result', [MpesaResultController::class, 'transactionStatusResult']);
    Route::post('/transaction-status/timeout', [MpesaTimeoutController::class, 'transactionStatusTimeout']);
    Route::post('/reversal/result', [MpesaResultController::class, 'reversalResult']);
    Route::post('/reversal/timeout', [MpesaTimeoutController::class, 'reversalTimeout']);
});
