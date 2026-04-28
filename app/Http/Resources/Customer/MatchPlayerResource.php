<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class MatchPlayerResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'customer_id' => $this->customer_id,
            'name' => $this->customer?->user?->name,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
        ];
    }
}
