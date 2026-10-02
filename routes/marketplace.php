<?php

use App\Http\Controllers\Marketplace\AccountExtrasController;
use App\Http\Controllers\Marketplace\AdminController;
use App\Http\Controllers\Marketplace\ConversationController;
use App\Http\Controllers\Marketplace\GigController;
use App\Http\Controllers\Marketplace\JobController;
use App\Http\Controllers\Marketplace\MarketplaceReviewController;
use App\Http\Controllers\Marketplace\OrderController;
use App\Http\Controllers\Marketplace\TaxonomyController;
use Illuminate\Support\Facades\Route;

Route::get('/taxonomy/categories', [TaxonomyController::class, 'categories']);
Route::get('/taxonomy/skills', [TaxonomyController::class, 'skills']);
Route::get('/gigs', [GigController::class, 'index']);
Route::get('/gigs/{gig}', [GigController::class, 'show']);
Route::get('/sellers/{user}', [AccountExtrasController::class, 'sellerShow']);
Route::get('/jobs', [JobController::class, 'index']);
Route::get('/jobs/{job}', [JobController::class, 'show']);
Route::get('/announcements', [AccountExtrasController::class, 'announcements']);
Route::post('/stripe/webhook', [AccountExtrasController::class, 'stripeWebhook']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/gigs', [GigController::class, 'store']);
    Route::put('/gigs/{gig}', [GigController::class, 'update']);
    Route::post('/gigs/{gig}/submit', [GigController::class, 'submit']);
    Route::post('/gigs/{gig}/pause', [GigController::class, 'pause']);
    Route::delete('/gigs/{gig}', [GigController::class, 'destroy']);
    Route::post('/gigs/{gig}/gallery', [GigController::class, 'uploadGallery']);

    Route::get('/orders', [OrderController::class, 'index']);
    Route::post('/orders', [OrderController::class, 'store']);
    Route::get('/orders/{order}', [OrderController::class, 'show']);
    Route::post('/orders/{order}/pay', [OrderController::class, 'pay']);
    Route::post('/orders/{order}/requirements', [OrderController::class, 'requirements']);
    Route::post('/orders/{order}/deliver', [OrderController::class, 'deliver']);
    Route::post('/orders/{order}/revision', [OrderController::class, 'revision']);
    Route::post('/orders/{order}/complete', [OrderController::class, 'complete']);
    Route::post('/orders/{order}/cancel', [OrderController::class, 'cancel']);
    Route::post('/orders/{order}/cancel/accept', [OrderController::class, 'acceptCancel']);
    Route::post('/orders/{order}/dispute', [OrderController::class, 'dispute']);

    Route::post('/jobs', [JobController::class, 'store']);
    Route::put('/jobs/{job}', [JobController::class, 'update']);
    Route::post('/jobs/{job}/close', [JobController::class, 'close']);
    Route::post('/jobs/{job}/repost', [JobController::class, 'repost']);
    Route::post('/jobs/{job}/proposals', [JobController::class, 'propose']);
    Route::get('/jobs/{job}/proposals', [JobController::class, 'proposals']);
    Route::post('/proposals/{proposal}/shortlist', [JobController::class, 'shortlist']);
    Route::post('/proposals/{proposal}/hire', [JobController::class, 'hire']);
    Route::post('/proposals/{proposal}/withdraw', [JobController::class, 'withdrawProposal']);

    Route::get('/conversations', [ConversationController::class, 'index']);
    Route::post('/conversations', [ConversationController::class, 'open']);
    Route::get('/conversations/{conversation}', [ConversationController::class, 'show']);
    Route::post('/conversations/{conversation}/messages', [ConversationController::class, 'send']);

    Route::post('/order-reviews', [MarketplaceReviewController::class, 'store']);
    Route::put('/seller/profile', [AccountExtrasController::class, 'updateSeller']);
    Route::match(['get', 'post'], '/seller/portfolios', [AccountExtrasController::class, 'portfolios']);
    Route::post('/saved', [AccountExtrasController::class, 'save']);
    Route::delete('/saved', [AccountExtrasController::class, 'unsave']);
    Route::get('/saved', [AccountExtrasController::class, 'saved']);
    Route::post('/payouts', [AccountExtrasController::class, 'payout']);
    Route::get('/connects', [AccountExtrasController::class, 'connectBalance']);
    Route::post('/connects/purchase', [AccountExtrasController::class, 'buyConnects']);
    Route::post('/reports', [AccountExtrasController::class, 'report']);

    Route::middleware('admin')->prefix('admin')->group(function () {
        Route::get('/dashboard', [AdminController::class, 'dashboard']);
        Route::get('/users', [AdminController::class, 'users']);
        Route::post('/users/{user}/ban', [AdminController::class, 'banUser']);
        Route::get('/gigs/pending', [AdminController::class, 'pendingGigs']);
        Route::post('/gigs/{gig}/approve', [AdminController::class, 'approveGig']);
        Route::post('/jobs/{job}/moderate', [AdminController::class, 'moderateJob']);
        Route::post('/orders/{order}/force', [AdminController::class, 'forceOrder']);
        Route::get('/ledger', [AdminController::class, 'ledger']);
        Route::get('/payouts', [AdminController::class, 'payouts']);
        Route::post('/payouts/{payout}/approve', [AdminController::class, 'approvePayout']);
        Route::get('/disputes', [AdminController::class, 'disputes']);
        Route::post('/disputes/{dispute}/resolve', [AdminController::class, 'resolveDispute']);
        Route::get('/reports', [AdminController::class, 'reports']);
        Route::post('/categories', [AdminController::class, 'storeCategory']);
        Route::post('/skills', [AdminController::class, 'storeSkill']);
        Route::match(['get', 'post'], '/settings', [AdminController::class, 'settings']);
        Route::match(['get', 'post'], '/announcements', [AdminController::class, 'announcements']);
    });
});
