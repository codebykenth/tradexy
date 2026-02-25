<?php

use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MigrateBalancesController;
use App\Http\Controllers\MigrateTradesController;
use App\Http\Controllers\TradeController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {
    Route::put('analyze/{id}', [AiAnalysisController::class, 'analyze'])->middleware('throttle:ai-analysis');

    Route::get('migrate-trades', [MigrateTradesController::class, 'migrate']);
    Route::get('migrate-balances', [MigrateBalancesController::class, 'migrate']);

});

// Authentication Routes
Route::middleware(['throttle:auth'])->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('register', [RegisterController::class, 'store']);
        Route::post('login', [LoginController::class, 'authenticate']);
        Route::get('/auth/{provider}', [LoginController::class, 'redirectToProvider']);
        Route::get('/auth/{provider}/callback', [LoginController::class, 'handleProviderCallback']);
    });

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth');
});

// Read Routes
Route::middleware(['throttle:read'])->group(function () {
    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });

    Route::middleware('guest')->group(function () {
        Route::get('/', function () {
            return view('index');
        });
        Route::get('login', [LoginController::class, 'index'])->name('login');
        Route::get('register', [RegisterController::class, 'index'])->name('register');
        Route::get('forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
    });

    Route::middleware('auth')->group(function () {
        Route::get('dashboard', [DashboardController::class, 'index']);
        Route::resource('trades', TradeController::class)->only(['index', 'show', 'create', 'edit']);
    });

    Route::get('/test-account-balance', [BalanceController::class, 'testBalance']);
    Route::get('/test-trades', [TradeController::class, 'testTrades']);
});

// Write Routes (Create, Update Delete)
Route::middleware(['throttle:write', 'auth'])->group(function () {
    Route::resource('trades', TradeController::class)->only(['store', 'update', 'destroy']);
});

Route::get('balances', [BalanceController::class, 'index']);