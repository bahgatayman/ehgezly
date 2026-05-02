<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class CustomerProfileResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->user;

        return [
            'id' => $user?->id,
            'name' => $user?->name,
            'email' => $user?->email,
            'phone' => $user?->phone,
            'profile_image' => $user?->profile_image,
            'can_book' => $this->can_book,
            'stats' => [
                'booking_count' => $this->bookingCount(),
                'active_bookings_count' => $this->activeBookingsCount(),
                'completed_bookings_count' => $this->completedBookingsCount(),
                'cancelled_bookings_count' => $this->cancelledBookingsCount(),
                'rejected_bookings_count' => $this->rejectedBookingsCount(),
            ],
        ];
    }
}
