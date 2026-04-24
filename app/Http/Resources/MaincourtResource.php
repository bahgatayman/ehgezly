<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MaincourtResource extends JsonResource
{
    public function toArray($request): array
    {
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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'payment_methods' => PaymentMethodResource::collection($this->whenLoaded('paymentMethods')),
            'working_hours' => WorkingHourResource::collection($this->whenLoaded('workingHours')),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return new ImageResource($this->primaryImage);
            }),
        ];
    }
}
