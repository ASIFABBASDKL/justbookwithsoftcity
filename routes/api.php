<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceUser\ServiceUserController;
use App\Http\Controllers\ServiceProvider\ServiceProviderController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\ServiceAndPricing\ServiceAndPricingController;
use App\Http\Controllers\PrivacyAndSecurity\PrivacyAndSecurityController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Chat\ServiceUserAndProviderChatController;
use App\Http\Controllers\Payment\PaymentTransactionController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\TwoStep\TwoStepVerificationController;
use App\Http\Controllers\BiometricLogin\BiometricLoginController;
use App\Http\Controllers\ChatWithAi\ChatWithAiController;
use App\Http\Controllers\Seller\SellerOnboardingController;

/*
|--------------------------------------------------------------------------
| Public (no token)
|--------------------------------------------------------------------------
*/
Route::middleware('throttle:auth')->group(function () {
    Route::post('/register-api', [AuthController::class, 'registerApi']);
    Route::post('/verify-email', [AuthController::class, 'verifyEmail']);
    Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);
    Route::post('/login-api', [AuthController::class, 'loginApi']);
    Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
    Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp']);
    Route::post('/reset-password', [AuthController::class, 'resetPassword']);

    Route::post('/biometric/verify', [BiometricLoginController::class, 'verify']);

    Route::post('/two-step/send-otp', [TwoStepVerificationController::class, 'sendOtp']);
    Route::post('/two-step/verify-email', [TwoStepVerificationController::class, 'verifyEmailOtp']);
    Route::post('/two-step/verify-phone', [TwoStepVerificationController::class, 'verifyPhoneOtp']);
});

Route::get('/categories', [ServiceAndPricingController::class, 'getAllCategories']);
Route::get('/categories/{category}/subcategories', [ServiceAndPricingController::class, 'getSubcategoriesByCategory']);
Route::get(
    '/categories/{category}/subcategories/{subcategory}/providers',
    [ServiceAndPricingController::class, 'getProvidersByCategoryAndSubcategory']
);
Route::get('/providers/{providerId}/services', [ServiceAndPricingController::class, 'getServicesByProvider']);
Route::get('provider/{id}/summary', [ServiceProviderController::class, 'providerSummary']);
Route::get('/reviews/provider/{provider_id}', [ReviewController::class, 'getProviderReviews']);
Route::get('/privacy-security/show', [PrivacyAndSecurityController::class, 'show']);

/*
|--------------------------------------------------------------------------
| Protected (Sanctum)
|--------------------------------------------------------------------------
*/
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout-api', [AuthController::class, 'logoutApi']);
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/become-seller', [SellerOnboardingController::class, 'becomeSeller']);
    Route::post('/change-password', [AuthController::class, 'changePassword']);
    Route::get('/provider/{id}/device-token', [AuthController::class, 'getProviderDeviceToken']);
    Route::get('/service-user/{id}/device-token', [AuthController::class, 'getServiceUserDeviceToken']);
    Route::get('/service-users', [AuthController::class, 'getAllServiceUsers']);
    Route::get('/service-providers', [AuthController::class, 'getAllServiceProviders']);

    Route::get('/two-step/status/{user_id}', [TwoStepVerificationController::class, 'getTwoStepStatus']);
    Route::post('/two-step/deactivate/{user_id}', [TwoStepVerificationController::class, 'deactivateTwoStepStatus']);

    Route::post('/biometric/store', [BiometricLoginController::class, 'store']);
    Route::post('/biometric/deactivate', [BiometricLoginController::class, 'deactivate']);

    Route::put('/service-user/{id}', [ServiceUserController::class, 'updateServiceUserApi']);
    Route::get('service-user/{id}/summary', [ServiceUserController::class, 'userSummary']);
    Route::put('service-user/{id}/update-summary', [ServiceUserController::class, 'updateProfileSummary']);

    Route::put('/service-providers/{id}', [ServiceProviderController::class, 'updateServiceProviderApi']);
    Route::put('/providers/{providerId}/profile', [ServiceProviderController::class, 'updateProfileApi']);
    Route::get('provider/{id}/today-earnings', [ServiceProviderController::class, 'todayEarningsAndReviews']);

    Route::post('/payments', [PaymentController::class, 'storePaymentMethodApi']);
    Route::get('/payments/provider/{providerId}', [PaymentController::class, 'getPaymentsByProvider']);
    Route::get('/payments/user/{userId}', [PaymentController::class, 'getPaymentsByUser']);
    Route::post('/payments/update', [PaymentController::class, 'updatePaymentMethodApi']);

    Route::post('/bookings/by-provider', [BookingController::class, 'getBookingsApi']);
    Route::post('/bookings/store', [BookingController::class, 'storeBookingApi']);
    Route::post('/bookings/update-status', [BookingController::class, 'updateBookingStatusApi']);
    Route::post('/bookings/by-user', [BookingController::class, 'getBookingsByUserApi']);

    Route::post('/providers/{providerId}/services', [ServiceAndPricingController::class, 'storeServiceProvider']);
    Route::put('/services/{serviceId}', [ServiceAndPricingController::class, 'updateService']);
    Route::delete('/services/{serviceId}', [ServiceAndPricingController::class, 'deleteService']);

    Route::post('/privacy-security/store', [PrivacyAndSecurityController::class, 'store']);
    Route::post('/privacy-security/accept', [PrivacyAndSecurityController::class, 'accept']);
    Route::post('/privacy-security/reject', [PrivacyAndSecurityController::class, 'reject']);

    Route::get('/providers/{providerId}/wallet', [WalletController::class, 'showWalletApi']);
    Route::post('/payment-transactions/store', [PaymentTransactionController::class, 'transactionStore'])
        ->name('payment-transactions.store');
    Route::post('/withdraw', [PaymentTransactionController::class, 'withdraw']);
    Route::get('/providers/{id}/transactions', [PaymentTransactionController::class, 'getAllTransactions']);

    Route::post('/reviews/submit', [ReviewController::class, 'submitReview']);

    Route::post('/send-message', [ServiceUserAndProviderChatController::class, 'sendMessage']);
    Route::get('/user-chats/{serviceUserId}', [ServiceUserAndProviderChatController::class, 'getChatsByUserId']);
    Route::get('/provider-chats/{serviceProviderId}', [ServiceUserAndProviderChatController::class, 'getChatsByProviderId']);
    Route::post('/get-messages', [ServiceUserAndProviderChatController::class, 'getMessages']);

    Route::post('/notifications/send', [NotificationController::class, 'send']);
    Route::get('/notifications/user/{userId}', [NotificationController::class, 'getUserNotifications']);

    Route::post('/chat-with-ai/store', [ChatWithAiController::class, 'chatWithAiStore']);
});

require __DIR__.'/marketplace.php';
