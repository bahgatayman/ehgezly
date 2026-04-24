<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\DashboardResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Courtowner;
use App\Models\Maincourt;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class DashboardController extends Controller
{
    public function index(): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourtIds = Maincourt::where('owner_id', $owner->id)->pluck('id');
        $courtIds = Court::whereIn('maincourt_id', $maincourtIds)->pluck('id');

        $totalBookingsQuery = Booking::whereIn('court_id', $courtIds);

        $recentBookings = Booking::whereIn('court_id', $courtIds)
            ->with(['customer.user', 'timeslot', 'court.maincourt'])
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $unreadNotifications = Notification::where('user_id', $owner->user_id)
            ->where('is_read', false)
            ->count();

        $data = [
            'total_maincourts' => $maincourtIds->count(),
            'total_courts' => $courtIds->count(),
            'total_bookings' => (clone $totalBookingsQuery)->count(),
            'pending_bookings' => (clone $totalBookingsQuery)->where('status', 'pending')->count(),
            'confirmed_bookings' => (clone $totalBookingsQuery)->where('status', 'confirmed')->count(),
            'completed_bookings' => (clone $totalBookingsQuery)->where('status', 'completed')->count(),
            'rejected_bookings' => (clone $totalBookingsQuery)->where('status', 'rejected')->count(),
            'total_revenue' => $owner->total_revenue,
            'app_due_amount' => $owner->app_due_amount,
            'remaining_balance' => $owner->remaining_balance,
            'recent_bookings' => $recentBookings,
            'unread_notifications_count' => $unreadNotifications,
        ];

        return $this->successResponse('Dashboard retrieved.', new DashboardResource($data));
    }

    private function getOwner(): ?Courtowner
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Courtowner::where('user_id', $user->id)->first();
    }

    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
