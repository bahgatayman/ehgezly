<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'total_price' => $this->total_price,
            'status' => $this->status,
            'receipt_image_url' => $this->receipt_image_url,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'customer' => $this->whenLoaded('customer', function () {
                $user = $this->customer->user;
                return [
                    'id' => $this->customer->id,
                    'name' => $user?->name,
                    'phone' => $user?->phone,
                    'email' => $user?->email,
                ];
            }),
            'court' => $this->whenLoaded('court', function () {
                return [
                    'id' => $this->court->id,
                    'name' => $this->court->name,
                    'type' => $this->court->type,
                ];
            }),
            'maincourt' => $this->whenLoaded('court', function () {
                $maincourt = $this->court->maincourt;
                return $maincourt ? [
                    'id' => $maincourt->id,
                    'name' => $maincourt->name,
                ] : null;
            }),
            'timeslot' => $this->whenLoaded('timeslot', function () {
                return [
                    'id' => $this->timeslot->id,
                    'date' => $this->timeslot->date,
                    'start_time' => $this->timeslot->start_time,
                    'end_time' => $this->timeslot->end_time,
                ];
            }),
            'payment_method' => $this->whenLoaded('paymentMethod', function () {
                return [
                    'id' => $this->paymentMethod->id,
                    'type' => $this->paymentMethod->type,
                    'identifier' => $this->paymentMethod->identifier,
                ];
            }),
        ];
    }
}
