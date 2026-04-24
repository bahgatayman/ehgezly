<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\AmenityResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\WorkingHourResource;
use Illuminate\Http\Resources\Json\JsonResource;

class MaincourtResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'address' => $this->address,
            'map_link' => $this->map_link,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'courts_count' => $this->when(isset($this->courts_count), $this->courts_count),
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return new ImageResource($this->primaryImage);
            }),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'amenities' => AmenityResource::collection($this->whenLoaded('amenities')),
            'working_hours' => WorkingHourResource::collection($this->whenLoaded('workingHours')),
            'payment_methods' => PaymentMethodResource::collection($this->whenLoaded('paymentMethods')),
            'courts' => CourtResource::collection($this->whenLoaded('courts')),
        ];
    }
}
