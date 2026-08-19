<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\ServiceUser\ServiceUserController;
use App\Http\Controllers\ServiceProvider\ServiceProviderController;
use App\Http\Controllers\Payment\PaymentController;
use App\Http\Controllers\ServiceArea\ServiceAreaController;
use App\Http\Controllers\Booking\BookingController;
use App\Http\Controllers\Availability\AvailabilityController;
use App\Http\Controllers\ServiceAndPricing\ServiceAndPricingController;
use App\Http\Controllers\EnableLocation\EnableLocationController;
use App\Http\Controllers\PrivacyAndSecurity\PrivacyAndSecurityController;
use App\Http\Controllers\Wallet\WalletController;
use App\Http\Controllers\Review\ReviewController;
use App\Http\Controllers\Chat\ServiceUserAndProviderChatController;
use App\Http\Controllers\Calling\CallController;
use App\Http\Controllers\Payment\PaymentTransactionController;
use App\Http\Controllers\Notification\NotificationController;
use App\Http\Controllers\TwoStep\TwoStepVerificationController;
use App\Http\Controllers\BiometricLogin\BiometricLoginController;
use App\Http\Controllers\ChatWithAi\ChatWithAiController;


Route::post('/register-api', [AuthController::class, 'registerApi']);
Route::post('/verify-email', [AuthController::class, 'verifyEmail']);   // ✅ Verify Email OTP
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

//two-step verification

Route::post('/two-step/send-otp', [TwoStepVerificationController::class, 'sendOtp']);
Route::post('/two-step/verify-email', [TwoStepVerificationController::class, 'verifyEmailOtp']);
Route::post('/two-step/verify-phone', [TwoStepVerificationController::class, 'verifyPhoneOtp']);
Route::get('/two-step/status/{user_id}', [TwoStepVerificationController::class, 'getTwoStepStatus']);
Route::post('/two-step/deactivate/{user_id}', [TwoStepVerificationController::class, 'deactivateTwoStepStatus']);

//fingerprint and face
Route::post('/biometric/store', [BiometricLoginController::class, 'store']);
Route::post('/biometric/verify', [BiometricLoginController::class, 'verify']);
Route::post('/biometric/deactivate', [BiometricLoginController::class, 'deactivate']);






//Service-User
Route::put('/service-user/{id}', [ServiceUserController::class, 'updateServiceUserApi']);
Route::get('service-user/{id}/summary', [ServiceUserController::class, 'userSummary']);
Route::put('service-user/{id}/update-summary', [ServiceUserController::class, 'updateProfileSummary']);

//Service-Provider
Route::put('/service-providers/{id}', [ServiceProviderController::class, 'updateServiceProviderApi']);
Route::put('/providers/{providerId}/profile', [ServiceProviderController::class, 'updateProfileApi']);
Route::get('provider/{id}/today-earnings', [ServiceProviderController::class, 'todayEarningsAndReviews']);
Route::get('provider/{id}/summary', [ServiceProviderController::class, 'providerSummary']);


//Paymentmethod
Route::post('/payments', [PaymentController::class, 'storePaymentMethodApi']);
// Fetch by Service Provider
Route::get('/payments/provider/{providerId}', [PaymentController::class, 'getPaymentsByProvider']);
// Fetch by Service User
Route::get('/payments/user/{userId}', [PaymentController::class, 'getPaymentsByUser']);
//update payment method
Route::post('/payments/update', [PaymentController::class, 'updatePaymentMethodApi']);


//Service Area
Route::post('/service-areas', [ServiceAreaController::class, 'storeServiceAreaApi']); 
Route::get('/service-areas/provider/{id}', [ServiceAreaController::class, 'getServiceAreasByProvider']);
Route::put('/service-areas/{id}', [ServiceAreaController::class, 'updateServiceAreaApi']);
Route::delete('/service-areas/{id}', [ServiceAreaController::class, 'deleteServiceAreaApi']);

//Booking
Route::post('/bookings/by-provider', [BookingController::class, 'getBookingsApi']);
Route::post('/bookings/store', [BookingController::class, 'storeBookingApi']);
Route::post('/bookings/update-status', [BookingController::class, 'updateBookingStatusApi']);
Route::post('/bookings/{id}/cancel', [BookingController::class, 'cancelBookingApi']);
Route::post('/bookings/by-user', [BookingController::class, 'getBookingsByUserApi']);


//availbilty

Route::post('/providers/{providerId}/availability', [AvailabilityController::class, 'storeAvailabilityApi']);
Route::get('/availability/{providerId}', [AvailabilityController::class, 'getAvailabilityApi']);   // Get availability
Route::put('/providers/{providerId}/availability', [AvailabilityController::class, 'updateAvailabilityApi']);


//Service-and-pricing
Route::post('/providers/{providerId}/services', [ServiceAndPricingController::class, 'storeServiceProvider']);
Route::get('/providers/{providerId}/services', [ServiceAndPricingController::class, 'getServicesByProvider']);
Route::get('/categories', [ServiceAndPricingController::class, 'getAllCategories']);
Route::get('/categories/{category}/subcategories', [ServiceAndPricingController::class, 'getSubcategoriesByCategory']);
// Route::get('/subcategories/{subcategory}/providers', [ServiceAndPricingController::class, 'getProvidersBySubcategory']);
Route::get(
    '/categories/{category}/subcategories/{subcategory}/providers',
    [ServiceAndPricingController::class, 'getProvidersByCategoryAndSubcategory']
);
Route::put('/services/{serviceId}', [ServiceAndPricingController::class, 'updateService']);
Route::delete('/services/{serviceId}', [ServiceAndPricingController::class, 'deleteService']);

//

Route::post('/enable-location/store', [EnableLocationController::class, 'store']);
Route::get('/enable-location/{service_provider_id}', [EnableLocationController::class, 'show']);
//

Route::post('/privacy-security/store', [PrivacyAndSecurityController::class, 'store']);
Route::get('/privacy-security/show', [PrivacyAndSecurityController::class, 'show']);
Route::post('/privacy-security/accept', [PrivacyAndSecurityController::class, 'accept']);
Route::post('/privacy-security/reject', [PrivacyAndSecurityController::class, 'reject']);
//

Route::get('/providers/{providerId}/wallet', [WalletController::class, 'showWalletApi']);
//Reviews
Route::post('/reviews/submit', [ReviewController::class, 'submitReview']);
Route::get('/reviews', [ReviewController::class, 'getReviews']);
Route::get('/reviews/provider/{provider_id}', [ReviewController::class, 'getProviderReviews']);

//chat
Route::post('/send-message', [ServiceUserAndProviderChatController::class, 'sendMessage']);
Route::get('/user-chats/{serviceUserId}', [ServiceUserAndProviderChatController::class, 'getChatsByUserId']);
Route::get('/provider-chats/{serviceProviderId}', [ServiceUserAndProviderChatController::class, 'getChatsByProviderId']);
Route::post('/get-messages', [ServiceUserAndProviderChatController::class, 'getMessages']);

//call history


Route::post('/calls/store', [CallController::class, 'storeCall']);            // Store call
Route::get('/calls/provider/{id}', [CallController::class, 'getAllByProviderId']); // Get calls by provider
Route::get('/calls/user/{id}', [CallController::class, 'getAllByServiceUserId']);  // Get calls by service user


// Store new payment transaction
Route::post('/payment-transactions/store', [PaymentTransactionController::class, 'transactionstore'])
    ->name('payment-transactions.store');
Route::post('/withdraw', [PaymentTransactionController::class, 'withdraw']);
Route::get('/providers/{id}/transactions', [PaymentTransactionController::class, 'getAllTransactions']);



Route::post('/notifications/send', [NotificationController::class, 'send']);
Route::get('/notifications/user/{userId}', [NotificationController::class, 'getUserNotifications']);

//chat with ai
Route::post('/chat-with-ai/store', [ChatWithAiController::class, 'chatWithAiStore']);
