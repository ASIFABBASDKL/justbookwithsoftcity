<?php

use Illuminate\Http\Request;
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

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| ⚠️ TODO (Phase 0): Abhi koi authentication nahi hai — saare endpoints
| khule hain. Sanctum lagne ke baad protected routes ko
| Route::middleware('auth:sanctum')->group(...) mein daalna hai.
| Tafseel ke liye dekho: PROJECT_PLAN.md → Phase 0.2
*/

/*
|--------------------------------------------------------------------------
| Auth
|--------------------------------------------------------------------------
*/
Route::post('/register-api', [AuthController::class, 'registerApi']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);   // Verify Email OTP
Route::post('/verify-phone', [AuthController::class, 'verifyPhone']);   // Verify Phone OTP
Route::post('/login-api', [AuthController::class, 'loginApi']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/verify-password-otp', [AuthController::class, 'verifyPasswordOtp']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);
Route::post('/change-password', [AuthController::class, 'changePassword']);
Route::get('/provider/{id}/device-token', [AuthController::class, 'getProviderDeviceToken']);
Route::get('/service-user/{id}/device-token', [AuthController::class, 'getServiceUserDeviceToken']);
Route::get('/service-users', [AuthController::class, 'getAllServiceUsers']);
Route::get('/service-providers', [AuthController::class, 'getAllServiceProviders']);

/*
|--------------------------------------------------------------------------
| Two-Step Verification
|--------------------------------------------------------------------------
*/
Route::post('/two-step/send-otp', [TwoStepVerificationController::class, 'sendOtp']);
Route::post('/two-step/verify-email', [TwoStepVerificationController::class, 'verifyEmailOtp']);
Route::post('/two-step/verify-phone', [TwoStepVerificationController::class, 'verifyPhoneOtp']);
Route::get('/two-step/status/{user_id}', [TwoStepVerificationController::class, 'getTwoStepStatus']);
Route::post('/two-step/deactivate/{user_id}', [TwoStepVerificationController::class, 'deactivateTwoStepStatus']);

/*
|--------------------------------------------------------------------------
| Biometric Login (fingerprint / face)
|--------------------------------------------------------------------------
*/
Route::post('/biometric/store', [BiometricLoginController::class, 'store']);
Route::post('/biometric/verify', [BiometricLoginController::class, 'verify']);
Route::post('/biometric/deactivate', [BiometricLoginController::class, 'deactivate']);

/*
|--------------------------------------------------------------------------
| Service User  (→ Phase 0.4 mein Buyer Profile banega)
|--------------------------------------------------------------------------
*/
Route::put('/service-user/{id}', [ServiceUserController::class, 'updateServiceUserApi']);
Route::get('service-user/{id}/summary', [ServiceUserController::class, 'userSummary']);
Route::put('service-user/{id}/update-summary', [ServiceUserController::class, 'updateProfileSummary']);

/*
|--------------------------------------------------------------------------
| Service Provider  (→ Phase 0.4 mein Seller Profile banega)
|--------------------------------------------------------------------------
*/
Route::put('/service-providers/{id}', [ServiceProviderController::class, 'updateServiceProviderApi']);
Route::put('/providers/{providerId}/profile', [ServiceProviderController::class, 'updateProfileApi']);
Route::get('provider/{id}/today-earnings', [ServiceProviderController::class, 'todayEarningsAndReviews']);
Route::get('provider/{id}/summary', [ServiceProviderController::class, 'providerSummary']);

/*
|--------------------------------------------------------------------------
| Payment Methods  (→ Phase 2 mein Stripe Connect replace karega)
|--------------------------------------------------------------------------
*/
Route::post('/payments', [PaymentController::class, 'storePaymentMethodApi']);
Route::get('/payments/provider/{providerId}', [PaymentController::class, 'getPaymentsByProvider']);
Route::get('/payments/user/{userId}', [PaymentController::class, 'getPaymentsByUser']);
Route::post('/payments/update', [PaymentController::class, 'updatePaymentMethodApi']);

/*
|--------------------------------------------------------------------------
| Bookings  (→ Phase 1 mein Orders ban jayega)
|--------------------------------------------------------------------------
*/
Route::post('/bookings/by-provider', [BookingController::class, 'getBookingsApi']);
Route::post('/bookings/store', [BookingController::class, 'storeBookingApi']);
Route::post('/bookings/update-status', [BookingController::class, 'updateBookingStatusApi']);
Route::post('/bookings/by-user', [BookingController::class, 'getBookingsByUserApi']);

/*
|--------------------------------------------------------------------------
| Services & Pricing  (→ Phase 1 mein Gigs + Packages ban jayega)
|--------------------------------------------------------------------------
*/
Route::post('/providers/{providerId}/services', [ServiceAndPricingController::class, 'storeServiceProvider']);
Route::get('/providers/{providerId}/services', [ServiceAndPricingController::class, 'getServicesByProvider']);
Route::get('/categories', [ServiceAndPricingController::class, 'getAllCategories']);
Route::get('/categories/{category}/subcategories', [ServiceAndPricingController::class, 'getSubcategoriesByCategory']);
Route::get(
    '/categories/{category}/subcategories/{subcategory}/providers',
    [ServiceAndPricingController::class, 'getProvidersByCategoryAndSubcategory']
);
Route::put('/services/{serviceId}', [ServiceAndPricingController::class, 'updateService']);
Route::delete('/services/{serviceId}', [ServiceAndPricingController::class, 'deleteService']);

/*
|--------------------------------------------------------------------------
| Privacy & Security
|--------------------------------------------------------------------------
*/
Route::post('/privacy-security/store', [PrivacyAndSecurityController::class, 'store']);
Route::get('/privacy-security/show', [PrivacyAndSecurityController::class, 'show']);
Route::post('/privacy-security/accept', [PrivacyAndSecurityController::class, 'accept']);
Route::post('/privacy-security/reject', [PrivacyAndSecurityController::class, 'reject']);

/*
|--------------------------------------------------------------------------
| Wallet & Transactions  (→ Phase 2 mein Escrow + Ledger add hoga)
|--------------------------------------------------------------------------
*/
Route::get('/providers/{providerId}/wallet', [WalletController::class, 'showWalletApi']);
Route::post('/payment-transactions/store', [PaymentTransactionController::class, 'transactionStore'])
    ->name('payment-transactions.store');
Route::post('/withdraw', [PaymentTransactionController::class, 'withdraw']);
Route::get('/providers/{id}/transactions', [PaymentTransactionController::class, 'getAllTransactions']);

/*
|--------------------------------------------------------------------------
| Reviews  (→ Phase 1.7 mein two-way banega)
|--------------------------------------------------------------------------
*/
Route::post('/reviews/submit', [ReviewController::class, 'submitReview']);
Route::get('/reviews/provider/{provider_id}', [ReviewController::class, 'getProviderReviews']);

/*
|--------------------------------------------------------------------------
| Chat  (→ Phase 1.6 mein Conversations + Messages banega)
|--------------------------------------------------------------------------
*/
Route::post('/send-message', [ServiceUserAndProviderChatController::class, 'sendMessage']);
Route::get('/user-chats/{serviceUserId}', [ServiceUserAndProviderChatController::class, 'getChatsByUserId']);
Route::get('/provider-chats/{serviceProviderId}', [ServiceUserAndProviderChatController::class, 'getChatsByProviderId']);
Route::post('/get-messages', [ServiceUserAndProviderChatController::class, 'getMessages']);

/*
|--------------------------------------------------------------------------
| Notifications
|--------------------------------------------------------------------------
*/
Route::post('/notifications/send', [NotificationController::class, 'send']);
Route::get('/notifications/user/{userId}', [NotificationController::class, 'getUserNotifications']);

/*
|--------------------------------------------------------------------------
| AI Assistant (Gemini)
|--------------------------------------------------------------------------
*/
Route::post('/chat-with-ai/store', [ChatWithAiController::class, 'chatWithAiStore']);
