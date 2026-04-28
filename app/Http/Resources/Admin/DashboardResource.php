<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class DashboardResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'total_users' => $this['total_users'],
            'total_customers' => $this['total_customers'],
            'total_courtowners' => $this['total_courtowners'],
            'pending_owners' => $this['pending_owners'],
            'total_maincourts' => $this['total_maincourts'],
            'verified_maincourts' => $this['verified_maincourts'],
            'pending_maincourts' => $this['pending_maincourts'],
            'total_bookings' => $this['total_bookings'],
            'pending_bookings' => $this['pending_bookings'],
            'confirmed_bookings' => $this['confirmed_bookings'],
            'completed_bookings' => $this['completed_bookings'],
            'total_revenue' => $this['total_revenue'],
            'total_app_earnings' => $this['total_app_earnings'],
            'pending_owner_payments' => $this['pending_owner_payments'],
            'recent_pending_owners' => $this['recent_pending_owners'],
            'recent_bookings' => $this['recent_bookings'],
            'unread_notifications_count' => $this['unread_notifications_count'],
        ];
    }
}
