<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\AppUser\CartController;
use App\Http\Controllers\AppUser\PointController;
use App\Http\Controllers\AppUser\ReviewController;
use App\Http\Controllers\AppUser\appAuthController;
use App\Http\Controllers\AppUser\BookingController;
use App\Http\Controllers\AppUser\GeneralController;
use App\Http\Controllers\AppUser\AppUsersController;
use App\Http\Controllers\AppUser\UserProfileController;
use App\Http\Controllers\AppUser\NotificationController;
use App\Http\Controllers\AppUser\SubscriptionController;
use App\Http\Controllers\AppUser\WalletController;
use App\Http\Controllers\Provider\BookingProviderController;
use App\Http\Controllers\Provider\providerAuthController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::group([
    'middleware' => 'api',
    'prefix' => 'app-user'
], function ($router) {
    //auth
    Route::post('/logout', [appAuthController::class, 'logout']);
    Route::post('/register', [appAuthController::class, 'register']);
    Route::post('/check_number', [AppUsersController::class, 'check_number']);
    Route::post('/check_opt', [AppUsersController::class, 'check_opt']);

    //booking
    Route::post('booking', [BookingController::class, 'bookService']);
    Route::get('offer-submitteds', [BookingController::class, 'offer_submitteds']);
    Route::post('/change-status-bookings/{id}', [BookingController::class, 'change_status_bookings']);
    ///coupon
   Route::post('check-coupon', [BookingController::class, 'checkCoupon']);
    //General
    Route::get('/services', [GeneralController::class, 'getAllServices'])->name('services');
    Route::get('/offers', [GeneralController::class, 'getOffers']);
    Route::get('/item-moved', [GeneralController::class, 'item_moved']);
    Route::get('/contact-us', [GeneralController::class, 'getContactUs']);
    Route::get('/about-us', [GeneralController::class, 'getAboutUs']);
    Route::get('/question', [GeneralController::class, 'getQuestion']);
    Route::get('/privacy', [GeneralController::class, 'getAllprivacy']);
    Route::get('/term', [GeneralController::class, 'getAllTerm']);
    Route::get('/setting', [GeneralController::class, 'getAllsetting']);
     /////////////////
     Route::get('/customer-service', [GeneralController::class, 'customer_service']);
    
   
    //user
    Route::get('/user/bookings', [BookingController::class, 'userBookings']);
    Route::get('/user-profile', [UserProfileController::class, 'index']);
    Route::post('/update-profile', [UserProfileController::class, 'updateProfile']);
    Route::get('/deactive-account', [UserProfileController::class, 'deactive_account']);
    ///notifications
    Route::get('/readNotifications-count', [NotificationController::class, 'count']);
    Route::get('/unreadNotifications-count', [NotificationController::class, 'unreadNotificationsCount']);
    Route::get('/notification-read', [NotificationController::class, 'NotificationRead']);
    Route::get('/notification-markasread', [NotificationController::class, 'MarkASRead']);
    Route::get('/notification-clear', [NotificationController::class, 'Clear']);
  
     //reviews route
    Route::post('/review', [ReviewController::class, 'store']);
    Route::post('/review/{review}', [ReviewController::class, 'update']);
    Route::delete('/review/{review}', [ReviewController::class, 'destroy']);
    ////wallet
    ///wallet
    Route::get('/user-balance', [WalletController::class, 'userBalance']);
    Route::post('/add-balance', [WalletController::class, 'addBalance']);
   
    Route::get('/user-notification', [GeneralController::class, 'notification']);
   Route::post('/user-notification', [GeneralController::class, 'SwipNotification']);
    });
   
    Route::get('/tabby-sucess', [BookingController::class, 'sucess'])->name('success-ur');
    Route::get('/tabby-cancel', [BookingController::class, 'cancel'])->name('cancel-ur');
    Route::get('/tabby-failure', [BookingController::class, 'failure'])->name('failure-ur');
    Route::get('/paylink-result', [BookingController::class, 'paylinkResult'])->name('paylink-result');

    ////////////
    Route::get('/tabby-sucess-subscription', [SubscriptionController::class, 'sucess'])->name('success-ur-subscription');
    Route::get('/tabby-cancel-subscription', [SubscriptionController::class, 'cancel'])->name('cancel-ur-subscription');
    Route::get('/tabby-failure-subscription', [SubscriptionController::class, 'failure'])->name('failure-ur-subscription');
    Route::get('/paylink-result-subscription', [SubscriptionController::class, 'paylinkResult'])->name('paylink-result-subscription');

    Route::group([
        'middleware' => 'api',
        'prefix' => 'provider'
    ], function ($router) {
        //auth
        Route::post('/logout', [providerAuthController::class, 'logout']);
        Route::post('/register', [providerAuthController::class, 'register']);
        Route::post('/check_number', [providerAuthController::class, 'check_number']);
        Route::post('/check_opt', [providerAuthController::class, 'check_opt']);
    });
    Route::group([
        'middleware' => 'auth:providers',
        'prefix' => 'provider'
    ], function ($router) {
        Route::get('/bookings', [BookingProviderController::class, 'Bookings']); 
        Route::post('/submit-booking/{id}', [BookingProviderController::class,'submitBooking']); 
        Route::post('/change-status-bookings/{id}', [BookingProviderController::class, 'change_status_bookings']);
    });

    require __DIR__ . '/dashboard.php';

