<?php

namespace App\Http\Resources\Customer;

use Illuminate\Http\Resources\Json\JsonResource;

class MaincourtRatingsResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'average_rating' => $this->resource['average_rating'],
            'ratings_count' => $this->resource['ratings_count'],
            'my_rating' => $this->resource['my_rating'],
            'ratings' => $this->resource['ratings'],
            'pagination' => $this->resource['pagination'] ?? null,
        ];
    }
}
