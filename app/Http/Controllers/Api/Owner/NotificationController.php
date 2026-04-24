<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\NotificationResource;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    public function index(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $notifications = Notification::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->get();

        $unreadCount = Notification::where('user_id', $user->id)
            ->where('is_read', false)
            ->count();

        return $this->successResponse('Notifications retrieved.', [
            'notifications' => NotificationResource::collection($notifications),
            'unread_count' => $unreadCount,
        ]);
    }

    public function markAsRead(int $id): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $notification = Notification::find($id);
        if (!$notification) {
            return $this->errorResponse('Notification not found.', 404);
        }

        if ($notification->user_id !== $user->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $notification->update(['is_read' => true]);

        return $this->successResponse('Notification marked as read.', new NotificationResource($notification));
    }

    public function markAllAsRead(): JsonResponse
    {
        $user = auth()->user();
        if (!$user) {
            return $this->errorResponse('Forbidden.', 403);
        }

        Notification::where('user_id', $user->id)->update(['is_read' => true]);

        return $this->successResponse('Notifications marked as read.', null);
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
