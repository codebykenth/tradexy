<?php

use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\AiAnalysisController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\BalanceController;
use App\Http\Controllers\DailyNewsController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MigrateBalancesController;
use App\Http\Controllers\MigrateStrategiesController;
use App\Http\Controllers\MigrateTradesController;
use App\Http\Controllers\PnlCalendarController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ScreenerController;
use App\Http\Controllers\SharedTradeController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\StrategyController;
use App\Http\Controllers\TradeBulkController;
use App\Http\Controllers\TradeController;
use App\Http\Controllers\TradingModeController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/user', function () {
    return Auth::user();
})->middleware('auth');

Route::middleware('auth')->group(function () {
    Route::put('analyze/{id}', [AiAnalysisController::class, 'analyze'])
        ->middleware('throttle:ai-analysis')
        ->name('ai.analyze');
    Route::delete('analyze/{id}', [AiAnalysisController::class, 'destroy'])
        ->name('ai.analyze.destroy');
});

// Developer-only migration routes
Route::middleware(['auth', 'can:developer'])->group(function () {
    Route::get('migrate-trades', [MigrateTradesController::class, 'migrate']);
    Route::get('migrate-balances', [MigrateBalancesController::class, 'migrate']);
    Route::get('migrate-strategies', [MigrateStrategiesController::class, 'migrate']);
});

// Authentication Routes
Route::middleware(['throttle:auth'])->group(function () {
    Route::middleware('guest')->group(function () {
        Route::post('register', [RegisterController::class, 'store']);
        Route::post('login', [LoginController::class, 'authenticate']);
        Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');
        Route::get('reset-password/{token}', [ForgotPasswordController::class, 'edit'])->name('password.reset');
        Route::post('reset-password', [ForgotPasswordController::class, 'update'])->name('password.update');
        Route::get('/auth/{provider}', [LoginController::class, 'redirectToProvider']);
        Route::get('/auth/{provider}/callback', [LoginController::class, 'handleProviderCallback']);
    });

    Route::post('logout', [LoginController::class, 'logout'])->middleware('auth');
});

// Read Routes
Route::middleware(['throttle:read'])->group(function () {
    Route::get('/sitemap.xml', [SitemapController::class, 'index']);

    Route::view('terms-and-conditions', 'legal.terms')->name('terms');
    Route::view('privacy-policy', 'legal.privacy')->name('privacy');
    Route::view('data-deletion', 'legal.deletion')->name('deletion');

    // Public shared trade view (no auth required)
    Route::get('shared/trades/{token}', [SharedTradeController::class, 'show'])->name('trades.shared');

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
        Route::get('pnl-calendar', [PnlCalendarController::class, 'index']);
        Route::get('trades/gallery', [TradeController::class, 'gallery'])->name('trades.gallery');
        Route::resource('trades', TradeController::class)->only(['index', 'show', 'create', 'edit']);
        Route::resource('balances', BalanceController::class)->only(['index', 'create']);
        Route::resource('strategies', StrategyController::class)->only(['index', 'create', 'show', 'edit']);
        Route::get('profile', [ProfileController::class, 'show'])->name('profile.show');

        // AI Market Insights
        Route::get('insights/latest', [DailyNewsController::class, 'latest'])->name('daily-news.latest');
        Route::get('insights', [DailyNewsController::class, 'index'])->name('daily-news.index');
        Route::get('insights/{id}', [DailyNewsController::class, 'show'])->name('daily-news.show');

        // Market Screener
        Route::get('screener', [ScreenerController::class, 'index'])->name('screener.index');
    });

    Route::fallback(function () {
        return response()->view('errors.404', [], 404);
    });
});

// Write Routes (Create, Update Delete)
Route::middleware(['throttle:write', 'auth'])->group(function () {
    Route::resource('trades', TradeController::class)->only(['store', 'update', 'destroy']);
    Route::resource('balances', BalanceController::class)->only(['store', 'update', 'destroy']);
    Route::resource('strategies', StrategyController::class)->only(['store', 'update', 'destroy']);
    Route::put('profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/change-password', [ProfileController::class, 'changePassword'])->name('profile.change-password');
    Route::delete('profile/remove-picture', [ProfileController::class, 'removeProfilePicture'])->name('profile.remove-picture');
    Route::delete('profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::post('trades/{id}/share', [SharedTradeController::class, 'generate'])->name('trades.share.generate');
    Route::delete('trades/{id}/share', [SharedTradeController::class, 'revoke'])->name('trades.share.revoke');
    Route::post('trading-mode', [TradingModeController::class, 'update'])->name('trading-mode.update');
    Route::post('trades/bulk', [TradeBulkController::class, 'handle'])->name('trades.bulk');

    // Admin Routes
    Route::middleware('can:developer')->prefix('admin')->name('admin.')->group(function () {
        Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');
        Route::get('/users', [AdminDashboardController::class, 'users'])->name('users');
        Route::get('/logs', [AdminDashboardController::class, 'logs'])->name('logs');
        Route::post('/toggle-maintenance', [AdminDashboardController::class, 'toggleMaintenance'])->name('maintenance.toggle');
        Route::post('/flush-cache', [AdminDashboardController::class, 'flushCache'])->name('cache.flush');
    });
});
