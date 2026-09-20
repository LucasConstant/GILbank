<?php

use App\Enums\UserRole;
use App\Http\Controllers\Api\AccountController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\InvestmentController;
use App\Http\Controllers\Api\PixController;
use App\Http\Controllers\Api\StatementController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // A API é exclusiva do papel "cliente" — gerentes usam sessão via Breeze (routes/web.php).
    Route::middleware('role:'.UserRole::Customer->value)->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::get('/balance', [AccountController::class, 'balance']);
        Route::get('/investments', [AccountController::class, 'balance']);

        Route::get('/statement', [StatementController::class, 'index']);

        Route::post('/pix', [PixController::class, 'store']);

        Route::post('/investments/apply', [InvestmentController::class, 'apply']);
        Route::post('/investments/redeem', [InvestmentController::class, 'redeem']);
    });
});
