<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class WorkingHourResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'maincourt_id' => $this->maincourt_id,
            'day_of_week' => $this->day_of_week,
            'open_time' => $this->open_time,
            'close_time' => $this->close_time,
            'is_open' => $this->is_open,
        ];
    }
}
