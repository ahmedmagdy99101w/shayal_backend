<?php

use App\Models\ControlBooking;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\AreaController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\CityController;
use App\Http\Controllers\Admin\RoleController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Admin\PointController;
use App\Http\Controllers\Admin\TermsController;
use App\Http\Controllers\Admin\OptionController;
use App\Http\Controllers\Admin\ReviewController;
use App\Http\Controllers\Admin\AboutUsController;
use App\Http\Controllers\Admin\BookingController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CouponsController;
use App\Http\Controllers\Admin\PrivacyController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\ManualNotificationController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\OptionTypeController;
use App\Http\Controllers\Admin\NotificationController;
use App\Http\Controllers\Admin\SubscriptionController;
use App\Http\Controllers\Admin\ControlBookingController;
use App\Http\Controllers\Admin\CoustomServiceController;
use App\Http\Controllers\Admin\OfferController;
use App\Http\Controllers\Admin\OfferControllerController;
use App\Http\Controllers\Admin\PaymentGatewayController;
use App\Models\CustomeService;

Route::group([
    'prefix' => 'auth'
], function ($router) {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);


});
Route::group([
    'middleware' => 'auth:users',
    'prefix' => 'dashboard'
], function ($router) {
    //users
    Route::get('/me', [UserController::class, 'me']);
Route::get('/users', [UserController::class, 'index']);
Route::get('/all_app_user', [UserController::class, 'app_user']);
Route::get('/users/{user}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::post('/users/{user}', [UserController::class, 'update']);
Route::delete('/users/{user}', [UserController::class, 'destroy']);
Route::get('getUserCount', [UserController::class, 'getUserCount']);
Route::get('getAppUserCount', [UserController::class, 'getAppUserCount']);
//roles
Route::get('/roles', [RoleController::class, 'index']);
Route::get('/roles/{role}', [RoleController::class, 'show']);
Route::post('/roles', [RoleController::class, 'store']);
Route::post('/roles/{role}', [RoleController::class, 'update']);
Route::delete('/roles/{role}', [RoleController::class, 'destroy']);
//services
Route::get('/services', [ServiceController::class, 'index']);
Route::get('service-details/{service}', [ServiceController::class, 'getServiceDetails']);
Route::get('/services/{service}', [ServiceController::class, 'show']);
Route::post('/services', [ServiceController::class, 'store']);
Route::post('/services/{service}', [ServiceController::class, 'update']);
Route::delete('/services/{service}', [ServiceController::class, 'destroy']);
Route::get('getServicesCount', [ServiceController::class, 'getServiceCount']);
//about_us
Route::get('about-us', [AboutUsController::class, 'index']);
Route::post('about-us', [AboutUsController::class, 'update']);
//terms
Route::get('terms', [TermsController::class, 'index']);
Route::post('terms', [TermsController::class, 'update']);
//offers
Route::get('offers', [OfferController::class, 'index']);
Route::post('offers', [OfferController::class, 'update']);
//privacy
Route::get('privacies', [PrivacyController::class, 'index']);
Route::post('privacies', [PrivacyController::class, 'update']);
//questions
Route::get('questions', [QuestionController::class, 'index']);
Route::post('questions', [QuestionController::class, 'store']);
Route::get('questions/{question}', [QuestionController::class, 'show']);
Route::post('questions/{question}', [QuestionController::class, 'update']);
Route::delete('questions/{question}', [QuestionController::class, 'destroy']);
//Contact
Route::get('contact', [ContactController::class, 'index']);
Route::post('contact', [ContactController::class, 'update']);
Route::get('contact-us', [ContactController::class, 'contact_us']);
//setting
Route::get('/setting', [SettingController::class, 'index']);
Route::post('/setting', [SettingController::class, 'store']);
//booking
Route::get('/bookings', [BookingController::class, 'index']);
Route::get('/getBookingCount', [BookingController::class, 'getBookingCount']);
Route::post('/bookings/{id}/status', [BookingController::class, 'changeBookingStatus']);
Route::get('/bookings/{id}', [BookingController::class, 'show']);


// subscription

Route::post('/suscriptions', [SubscriptionController::class, 'createSubscriptions']);
Route::post('/suscriptions/{id}/status', [SubscriptionController::class,'updateSubscriptionStatus']);
Route::post('/suscriptions/{id}', [SubscriptionController::class,'updateSubscription']);
Route::get('/suscriptions/{id}', [SubscriptionController::class,'show']);
Route::get('/suscriptions', [SubscriptionController::class,'index']);
Route::get('getSubscriptionCount', [SubscriptionController::class, 'getSubscriptionCount']);
Route::delete('/suscriptions/{id}', [SubscriptionController::class, 'deleteSubscription']);


//payments getway
Route::get('/payments-getway', [PaymentGatewayController::class, 'index']);
Route::post('/tammara-update', [PaymentGatewayController::class, 'TammaraUpdate']);
Route::post('/tabby-update', [PaymentGatewayController::class, 'TabbyUpdate']);


///notifications
Route::get('/notification-read/{type}', [NotificationController::class, 'NotificationRead']);
Route::get('/notification-markasread/{type}', [NotificationController::class, 'MarkASRead']);
Route::get('/notification-clear/{type}', [NotificationController::class, 'Clear']);

//reports
Route::get('/all-order', [ReportsController::class, 'all_orders']);
Route::get('/order/{id}', [ReportsController::class, 'orderDetails']);
Route::get('/all-payments', [ReportsController::class, 'all_payments']);
Route::get('/all-subscription', [ReportsController::class, 'all_subscription']);
Route::get('/all-payments-subscription', [ReportsController::class, 'all_payments_subscription']);

///

Route::get('/coupons', [CouponsController::class, 'index']);
Route::post('/coupons', [CouponsController::class, 'store']);
Route::get('/coupons/{coupon}', [CouponsController::class, 'show']);
Route::post('/coupons/{coupon}', [CouponsController::class, 'update']);
Route::delete('/coupons/{coupon}', [CouponsController::class, 'destroy']);

//reviews
Route::get('/reviews', [ReviewController::class, 'index']);
Route::get('/reviews/{id}', [ReviewController::class, 'show']);
Route::delete('/reviews/{id}', [ReviewController::class, 'destroy']);


Route::get('/city', [CityController::class, 'index']);
Route::get('/city/{city}', [CityController::class, 'show']);
Route::post('/city', [CityController::class, 'store']);
Route::post('/city/{city}', [CityController::class, 'update']);
Route::delete('/city/{city}', [CityController::class, 'destroy']);


//area route

Route::get('/area', [AreaController::class, 'index']);
Route::get('/area/{area}', [AreaController::class, 'show']);
Route::post('/area', [AreaController::class, 'store']);
Route::post('/area/{area}', [AreaController::class, 'update']);
Route::delete('/area/{area}', [AreaController::class, 'destroy']);
Route::get('/areas/{city_id}', [AreaController::class, 'cityArea']);

//////////////
Route::post('/manual-notifications', [ManualNotificationController::class, 'store']);

Route::get('control_bookings', [ControlBookingController::class, 'index']);
Route::post('control_bookings', [ControlBookingController::class, 'store']);
Route::get('control_bookings/{control_bookings}', [ControlBookingController::class, 'show']);
Route::post('control_bookings/{id}', [ControlBookingController::class, 'update']);
Route::delete('control_bookings/{id}', [ControlBookingController::class, 'destroy']);
//point
Route::get('/balance', [PointController::class, 'index']);

/////////////////
Route::apiResource('option-types', OptionTypeController::class);
Route::apiResource('options', OptionController::class);
 Route::get('getOptionsBySubServiceId/{service}',[ OptionTypeController::class,'getOPtionTYpedService']);
//  Route::apiResource('customer-service', CoustomServiceController::class);
 Route::get('customer-service', [CoustomServiceController::class, 'index']);
 Route::post('customer-service', [CoustomServiceController::class, 'update']);
});

Route::post('/contact-us', [App\Http\Controllers\HomeController::class, 'contactUs']);
Route::get('/home-settings', [App\Http\Controllers\HomeController::class, 'Settings']);




