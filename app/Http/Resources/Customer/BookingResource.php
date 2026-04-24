<?php

namespace App\Http\Resources\Customer;

use App\Http\Resources\PaymentMethodResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BookingResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'customer_id' => $this->customer_id,
            'court_id' => $this->court_id,
            'timeslot_id' => $this->timeslot_id,
            'payment_method_id' => $this->payment_method_id,
            'total_price' => $this->total_price,
            'receipt_image_url' => $this->receipt_image_url,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'court' => $this->whenLoaded('court', function () {
                return new CourtResource($this->court);
            }),
            'timeslot' => $this->whenLoaded('timeslot', function () {
                return new TimeslotResource($this->timeslot);
            }),
            'payment_method' => $this->whenLoaded('paymentMethod', function () {
                return new PaymentMethodResource($this->paymentMethod);
            }),
        ];
    }
}
