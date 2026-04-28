<?php

namespace App\Http\Resources\Admin;

use App\Http\Resources\AmenityResource;
use App\Http\Resources\CourtResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\WorkingHourResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MaincourtResource extends JsonResource
{
    public function toArray($request): array
    {
        $owner = $this->whenLoaded('owner');
        $ownerUser = $owner?->user;

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'map_link' => $this->map_link,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'status' => $this->status,
            'is_verified' => $this->is_verified,
            'courts_count' => $this->courts_count ?? ($this->relationLoaded('courts') ? $this->courts->count() : null),
            'owner' => $ownerUser ? [
                'id' => $ownerUser->id,
                'name' => $ownerUser->name,
                'email' => $ownerUser->email,
                'phone' => $ownerUser->phone,
            ] : null,
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'payment_methods' => PaymentMethodResource::collection($this->whenLoaded('paymentMethods')),
            'working_hours' => WorkingHourResource::collection($this->whenLoaded('workingHours')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
        ];
    }
}
