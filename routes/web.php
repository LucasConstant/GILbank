<?php

use App\Enums\UserRole;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Web\AccountManager\AccountController;
use App\Http\Controllers\Web\AccountManager\LimitRequestController as AccountManagerLimitRequestController;
use App\Http\Controllers\Web\AccountManager\StatementController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\GeneralManager\AccountManagerController;
use App\Http\Controllers\Web\GeneralManager\AuditLogController;
use App\Http\Controllers\Web\GeneralManager\LimitRequestController as GeneralManagerLimitRequestController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return auth()->check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
});

Route::get('/dashboard', DashboardController::class)
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified', 'role:'.UserRole::GeneralManager->value])
    ->prefix('gerente-geral')
    ->name('general-manager.')
    ->group(function () {
        Route::resource('managers', AccountManagerController::class)
            ->parameters(['managers' => 'manager'])
            ->except(['show']);

        Route::get('limit-requests', [GeneralManagerLimitRequestController::class, 'index'])
            ->name('limit-requests.index');
        Route::post('limit-requests/{limitRequest}/approve', [GeneralManagerLimitRequestController::class, 'approve'])
            ->name('limit-requests.approve');
        Route::post('limit-requests/{limitRequest}/reject', [GeneralManagerLimitRequestController::class, 'reject'])
            ->name('limit-requests.reject');

        Route::get('audits', [AuditLogController::class, 'index'])
            ->name('audits.index');
    });

Route::middleware(['auth', 'verified', 'role:'.UserRole::AccountManager->value])
    ->prefix('gerente-conta')
    ->name('account-manager.')
    ->group(function () {
        Route::resource('accounts', AccountController::class);

        Route::post('accounts/{account}/block', [AccountController::class, 'block'])
            ->name('accounts.block');
        Route::post('accounts/{account}/unblock', [AccountController::class, 'unblock'])
            ->name('accounts.unblock');

        Route::get('accounts/{account}/statement', [StatementController::class, 'show'])
            ->name('accounts.statement');

        Route::get('limit-requests', [AccountManagerLimitRequestController::class, 'index'])
            ->name('limit-requests.index');
        Route::post('accounts/{account}/limit-requests', [AccountManagerLimitRequestController::class, 'store'])
            ->name('limit-requests.store');
    });

require __DIR__.'/auth.php';
