<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\DashboardResource;
use App\Http\Resources\Admin\OwnerResource;
use App\Http\Resources\Owner\BookingResource;
use App\Models\Booking;
use App\Models\Courtowner;
use App\Models\Maincourt;
use App\Models\Notification;
use App\Models\OwnerPayment;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $totalBookingsQuery = Booking::query();

        $recentPendingOwners = Courtowner::whereHas('user', function ($query) {
                $query->where('role', 'courtowner')->where('status', 'pending');
            })
            ->with(['user', 'maincourts'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $recentBookings = Booking::with(['court.maincourt', 'customer.user'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $adminId = auth()->id();
        $unreadNotifications = $adminId
            ? Notification::where('user_id', $adminId)->where('is_read', false)->count()
            : 0;

        $data = [
            'total_users' => User::count(),
            'total_customers' => User::where('role', 'customer')->count(),
            'total_courtowners' => User::where('role', 'courtowner')->where('status', 'active')->count(),
            'pending_owners' => User::where('role', 'courtowner')->where('status', 'pending')->count(),
            'total_maincourts' => Maincourt::count(),
            'verified_maincourts' => Maincourt::where('is_verified', true)->count(),
            'pending_maincourts' => Maincourt::where('is_verified', false)->where('status', 'active')->count(),
            'total_bookings' => (clone $totalBookingsQuery)->count(),
            'pending_bookings' => (clone $totalBookingsQuery)->where('status', 'pending')->count(),
            'confirmed_bookings' => (clone $totalBookingsQuery)->where('status', 'confirmed')->count(),
            'completed_bookings' => (clone $totalBookingsQuery)->where('status', 'completed')->count(),
            'total_revenue' => Courtowner::sum('total_revenue'),
            'total_app_earnings' => Courtowner::sum('app_due_amount'),
            'pending_owner_payments' => OwnerPayment::where('status', 'pending')->count(),
            'recent_pending_owners' => OwnerResource::collection($recentPendingOwners),
            'recent_bookings' => BookingResource::collection($recentBookings),
            'unread_notifications_count' => $unreadNotifications,
        ];

        return $this->successResponse('Dashboard retrieved.', new DashboardResource($data));
    }

    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }
}
