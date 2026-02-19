<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;




// For unauthenticated users
Route::middleware('guest')->group(function () {
    Route::get('/', function () {
        return view('index');
    });

    Route::get('login', [LoginController::class, 'index'])->name('login');
    Route::get('register', [RegisterController::class, 'index'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::post('login', [LoginController::class, 'authenticate']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'index'])->name('forgot-password');
});
// For authenticated users
Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'logout']);
    Route::get('dashboard', [DashboardController::class, 'index']);
});

Route::get('/auth/{provider}', [LoginController::class, 'redirectToProvider']);
Route::get('/auth/{provider}/callback', [LoginController::class, 'handleProviderCallback']);