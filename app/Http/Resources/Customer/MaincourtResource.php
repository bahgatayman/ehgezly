<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\AmenityResource;
use App\Http\Resources\ImageResource;
use App\Http\Resources\PaymentMethodResource;
use App\Http\Resources\WorkingHourResource;
use App\Http\Resources\Customer\RatingResource;
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
            'distance' => $this->when(isset($this->distance), function () {
                return round($this->distance, 2);
            }),
            'distance_text' => $this->when(isset($this->distance), function () {
                $distance = round($this->distance, 2);
                if ($distance < 1) {
                    return round($distance * 1000) . ' م';
                }

                return $distance . ' كم';
            }),
            'average_rating' => $this->when(
                $this->ratings_avg_rating !== null || $this->ratings_count !== null || $this->relationLoaded('ratings') || isset($this->average_rating),
                function () {
                    if (isset($this->average_rating)) {
                        return round((float) $this->average_rating, 1);
                    }

                    $avg = $this->ratings_avg_rating !== null ? $this->ratings_avg_rating : $this->averageRating();
                    return round((float) $avg, 1);
                }
            ),
            'ratings_count' => $this->when(
                $this->ratings_count !== null || $this->relationLoaded('ratings'),
                function () {
                    return $this->ratings_count !== null ? (int) $this->ratings_count : $this->ratingsCount();
                }
            ),
            'my_rating' => $this->when(isset($this->my_rating), function () {
                return $this->formatRating($this->my_rating);
            }),
            'recent_ratings' => $this->when(isset($this->recent_ratings), function () {
                return RatingResource::collection($this->recent_ratings);
            }),
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

    private function formatRating($rating): ?array
    {
        if (!$rating) {
            return null;
        }

        return [
            'rating' => $rating->rating,
            'comment' => $rating->comment,
            'created_at' => $rating->created_at?->toDateString(),
        ];
    }
}
