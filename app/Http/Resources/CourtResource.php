<?php

namespace App\Http\Resources;

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
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'primary_image' => $this->whenLoaded('primaryImage', function () {
                return new ImageResource($this->primaryImage);
            }),
            'images' => ImageResource::collection($this->whenLoaded('images')),
            'timeslots' => $this->whenLoaded('timeslots'),
        ];
    }
}
