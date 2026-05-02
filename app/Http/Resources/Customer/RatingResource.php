<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class RatingResource extends JsonResource
{
    public function toArray($request): array
    {
        $name = $this->customer?->user?->name;
        $firstName = $name ? explode(' ', trim($name))[0] : null;

        return [
            'rating' => $this->rating,
            'comment' => $this->comment,
            'customer_name' => $firstName,
            'created_at' => $this->created_at?->toDateString(),
        ];
    }
}
