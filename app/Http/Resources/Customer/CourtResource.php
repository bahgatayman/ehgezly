<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\ImageResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\WorkingHourResource;
use Illuminate\Http\Resources\Json\JsonResource;

class CourtResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'maincourt_id' => $this->maincourt_id,
            'name' => $this->name,
            'description' => $this->description,
            'type' => $this->type,
            'surface_type' => $this->surface_type,
            'price_per_hour' => $this->price_per_hour,
            'status' => $this->status,
            'is_open' => $this->is_open,
            'available_timeslots_count' => $this->when(isset($this->available_timeslots_count), $this->available_timeslots_count),
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return new ImageResource($this->primaryImage);
            }),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'maincourt' => $this->whenLoaded('maincourt', function () {
                return [
                    'id' => $this->maincourt->id,
                    'name' => $this->maincourt->name,
                    'address' => $this->maincourt->address,
                    'working_hours' => WorkingHourResource::collection($this->maincourt->workingHours),
                    'payment_methods' => PaymentMethodResource::collection($this->maincourt->paymentMethods),
                ];
            }),
        ];
    }
}
