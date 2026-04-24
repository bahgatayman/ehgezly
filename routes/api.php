<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Owner\AmenityController;
use App\Http\Controllers\Api\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Api\Owner\CourtController;
use App\Http\Controllers\Api\Owner\DashboardController;
use App\Http\Controllers\Api\Owner\ImageController;
use App\Http\Controllers\Api\Owner\MaincourtController;
use App\Http\Controllers\Api\Owner\NotificationController as OwnerNotificationController;
use App\Http\Controllers\Api\Owner\PaymentMethodController;
use App\Http\Controllers\Api\Owner\WorkingHourController;
use App\Http\Controllers\Api\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Api\Customer\CourtController as CustomerCourtController;
use App\Http\Controllers\Api\Customer\MaincourtController as CustomerMaincourtController;
use App\Http\Controllers\Api\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Api\Customer\TimeslotController as CustomerTimeslotController;


/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/signup', [AuthController::class, 'signup']);
Route::post('/login', [AuthController::class, 'login']);
Route::post('/forgot-password', [AuthController::class, 'forgotPassword']);
Route::post('/reset-password', [AuthController::class, 'resetPassword']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::prefix('owner')->middleware(['auth:sanctum', 'role:courtowner'])->group(function () {
    Route::post('maincourts', [MaincourtController::class, 'store']);
    Route::get('maincourts', [MaincourtController::class, 'index']);
    Route::get('maincourts/{id}', [MaincourtController::class, 'show']);
    Route::put('maincourts/{id}', [MaincourtController::class, 'update']);
    Route::delete('maincourts/{id}', [MaincourtController::class, 'destroy']);

    Route::post('maincourts/{maincourt_id}/courts', [CourtController::class, 'store']);
    Route::get('maincourts/{maincourt_id}/courts', [CourtController::class, 'index']);
    Route::get('maincourts/{maincourt_id}/courts/{id}', [CourtController::class, 'show']);
    Route::put('maincourts/{maincourt_id}/courts/{id}', [CourtController::class, 'update']);
    Route::delete('maincourts/{maincourt_id}/courts/{id}', [CourtController::class, 'destroy']);

    Route::post('maincourts/{id}/images', [ImageController::class, 'storeMaincourtImages']);
    Route::post('maincourts/{maincourt_id}/courts/{id}/images', [ImageController::class, 'storeCourtImages']);
    Route::delete('images/{image_id}', [ImageController::class, 'destroy']);

    Route::get('amenities', [AmenityController::class, 'index']);
    Route::post('maincourts/{id}/amenities', [AmenityController::class, 'sync']);

    Route::post('maincourts/{id}/working-hours', [WorkingHourController::class, 'store']);
    Route::get('maincourts/{id}/working-hours', [WorkingHourController::class, 'index']);

    Route::post('maincourts/{id}/payment-methods', [PaymentMethodController::class, 'store']);
    Route::get('maincourts/{id}/payment-methods', [PaymentMethodController::class, 'index']);
    Route::put('payment-methods/{id}', [PaymentMethodController::class, 'update']);
    Route::delete('payment-methods/{id}', [PaymentMethodController::class, 'destroy']);

    Route::get('bookings', [OwnerBookingController::class, 'index']);
    Route::get('bookings/{id}', [OwnerBookingController::class, 'show']);
    Route::put('bookings/{id}/confirm', [OwnerBookingController::class, 'confirm']);
    Route::put('bookings/{id}/reject', [OwnerBookingController::class, 'reject']);
    Route::put('bookings/{id}/complete', [OwnerBookingController::class, 'complete']);

    Route::get('notifications', [OwnerNotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [OwnerNotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [OwnerNotificationController::class, 'markAllAsRead']);

    Route::get('dashboard', [DashboardController::class, 'index']);
});

Route::prefix('customer')->middleware(['auth:sanctum', 'role:customer'])->group(function () {
    Route::get('maincourts', [CustomerMaincourtController::class, 'index']);
    Route::get('maincourts/{id}', [CustomerMaincourtController::class, 'show']);

    Route::get('maincourts/{maincourt_id}/courts', [CustomerCourtController::class, 'index']);
    Route::get('maincourts/{maincourt_id}/courts/{id}', [CustomerCourtController::class, 'show']);

    Route::get('courts/{court_id}/timeslots', [CustomerTimeslotController::class, 'index']);

    Route::post('bookings', [CustomerBookingController::class, 'store']);
    Route::get('bookings', [CustomerBookingController::class, 'index']);
    Route::get('bookings/{id}', [CustomerBookingController::class, 'show']);
    Route::delete('bookings/{id}', [CustomerBookingController::class, 'destroy']);

    Route::get('notifications', [CustomerNotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [CustomerNotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [CustomerNotificationController::class, 'markAllAsRead']);
});
