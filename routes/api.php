<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\Owner\AmenityController;
use App\Http\Controllers\Api\Owner\BookingController as OwnerBookingController;
use App\Http\Controllers\Api\Owner\CourtController;
use App\Http\Controllers\Api\Owner\DashboardController;
use App\Http\Controllers\Api\Owner\FinancialController;
use App\Http\Controllers\Api\Owner\ImageController;
use App\Http\Controllers\Api\Owner\MaincourtController;
use App\Http\Controllers\Api\Owner\NotificationController as OwnerNotificationController;
use App\Http\Controllers\Api\Owner\OwnerPaymentController as OwnerPaymentController;
use App\Http\Controllers\Api\Owner\PaymentMethodController;
use App\Http\Controllers\Api\Owner\WorkingHourController;
use App\Http\Controllers\Api\Customer\BookingController as CustomerBookingController;
use App\Http\Controllers\Api\Customer\CourtController as CustomerCourtController;
use App\Http\Controllers\Api\Customer\MaincourtController as CustomerMaincourtController;
use App\Http\Controllers\Api\Customer\NotificationController as CustomerNotificationController;
use App\Http\Controllers\Api\Customer\TimeslotController as CustomerTimeslotController;
use App\Http\Controllers\Api\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Api\Admin\OwnerController as AdminOwnerController;
use App\Http\Controllers\Api\Admin\MaincourtController as AdminMaincourtController;
use App\Http\Controllers\Api\Admin\OwnerPaymentController as AdminOwnerPaymentController;
use App\Http\Controllers\Api\Admin\NotificationController as AdminNotificationController;


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
Route::get('/test', function () {
    return response()->json(['status' => 'ok']);
});

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

    Route::get('financials', [FinancialController::class, 'index']);
    Route::get('app-payment-info', [OwnerPaymentController::class, 'appPaymentInfo']);
    Route::get('payments', [OwnerPaymentController::class, 'index']);
    Route::post('payments', [OwnerPaymentController::class, 'store']);
    Route::get('payments/{id}', [OwnerPaymentController::class, 'show']);
    Route::delete('payments/{id}', [OwnerPaymentController::class, 'destroy']);

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

Route::prefix('admin')->middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::get('dashboard', [AdminDashboardController::class, 'index']);

    Route::get('owners', [AdminOwnerController::class, 'index']);
    Route::get('owners/{id}', [AdminOwnerController::class, 'show']);
    Route::put('owners/{id}/approve', [AdminOwnerController::class, 'approve']);
    Route::put('owners/{id}/reject', [AdminOwnerController::class, 'reject']);
    Route::put('owners/{id}/suspend', [AdminOwnerController::class, 'suspend']);
    Route::put('owners/{id}/activate', [AdminOwnerController::class, 'activate']);
    Route::put('owners/{id}/commission', [AdminOwnerController::class, 'updateCommission']);

    Route::get('maincourts', [AdminMaincourtController::class, 'index']);
    Route::get('maincourts/{id}', [AdminMaincourtController::class, 'show']);
    Route::put('maincourts/{id}/verify', [AdminMaincourtController::class, 'verify']);
    Route::put('maincourts/{id}/suspend', [AdminMaincourtController::class, 'suspend']);

    Route::get('owner-payments', [AdminOwnerPaymentController::class, 'index']);
    Route::get('owner-payments/{id}', [AdminOwnerPaymentController::class, 'show']);
    Route::put('owner-payments/{id}/approve', [AdminOwnerPaymentController::class, 'approve']);
    Route::put('owner-payments/{id}/reject', [AdminOwnerPaymentController::class, 'reject']);

    Route::get('notifications', [AdminNotificationController::class, 'index']);
    Route::put('notifications/{id}/read', [AdminNotificationController::class, 'markAsRead']);
    Route::put('notifications/read-all', [AdminNotificationController::class, 'markAllAsRead']);
});
