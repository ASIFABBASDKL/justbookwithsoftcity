<?php

use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\WebAuthController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'home'])->name('home');

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [WebAuthController::class, 'login']);
    Route::get('/register', [WebAuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [WebAuthController::class, 'register']);
});

Route::middleware('auth')->group(function () {
    Route::post('/logout', [WebAuthController::class, 'logout'])->name('logout');
    Route::get('/app', [DashboardController::class, 'appHome'])->name('web.app');

    Route::get('/app/buyer', [DashboardController::class, 'buyer'])->name('web.buyer');

    Route::get('/app/seller/onboard', [DashboardController::class, 'sellerOnboard'])->name('web.seller.onboard');
    Route::post('/app/seller/onboard', [DashboardController::class, 'becomeSeller'])->name('web.seller.become');
    Route::get('/app/seller', [DashboardController::class, 'seller'])->middleware('seller')->name('web.seller');

    Route::get('/app/admin', [DashboardController::class, 'admin'])->middleware('admin')->name('web.admin');
});
