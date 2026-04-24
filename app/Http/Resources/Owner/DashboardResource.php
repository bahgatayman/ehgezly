<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total_maincourts' => $this['total_maincourts'],
            'total_courts' => $this['total_courts'],
            'total_bookings' => $this['total_bookings'],
            'pending_bookings' => $this['pending_bookings'],
            'confirmed_bookings' => $this['confirmed_bookings'],
            'completed_bookings' => $this['completed_bookings'],
            'rejected_bookings' => $this['rejected_bookings'],
            'total_revenue' => $this['total_revenue'],
            'app_due_amount' => $this['app_due_amount'],
            'remaining_balance' => $this['remaining_balance'],
            'recent_bookings' => BookingResource::collection($this['recent_bookings']),
            'unread_notifications_count' => $this['unread_notifications_count'],
        ];
    }
}
