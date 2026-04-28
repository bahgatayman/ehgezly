<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\ImageResource;
use Illuminate\Http\Resources\Json\JsonResource;

class OpenMatchResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'status' => $this->status,
            'required_players' => $this->required_players,
            'current_players' => $this->current_players,
            'spots_left' => max(0, $this->required_players - $this->current_players),
            'booking_id' => $this->booking_id,
            'created_at' => $this->created_at,
            'court' => $this->whenLoaded('court', function () {
                $maincourt = $this->court->maincourt;

                return [
                    'id' => $this->court->id,
                    'name' => $this->court->name,
                    'type' => $this->court->type,
                    'price_per_hour' => $this->court->price_per_hour,
                    'maincourt' => $maincourt ? [
                        'id' => $maincourt->id,
                        'name' => $maincourt->name,
                        'address' => $maincourt->address,
                        'primary_image' => $maincourt->primaryImage ? new ImageResource($maincourt->primaryImage) : null,
                    ] : null,
                ];
            }),
            'timeslot' => $this->whenLoaded('timeslot', function () {
                return new TimeslotResource($this->timeslot);
            }),
            'creator' => $this->whenLoaded('creator', function () {
                return [
                    'id' => $this->creator->id,
                    'name' => $this->creator->user?->name,
                ];
            }),
            'joined_players' => MatchPlayerResource::collection($this->whenLoaded('joinedPlayers')),
            'auth_status' => $this->when(isset($this->auth_status), $this->auth_status),
        ];
    }
}
