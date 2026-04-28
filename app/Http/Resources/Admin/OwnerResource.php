<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnerResource extends JsonResource
{
    public function toArray($request): array
    {
        $user = $this->whenLoaded('user');

        return [
            'id' => $this->id,
            'user' => $user ? [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
                'status' => $user->status,
            ] : null,
            'commission_percentage' => $this->commission_percentage,
            'total_revenue' => $this->total_revenue,
            'app_due_amount' => $this->app_due_amount,
            'remaining_balance' => $this->remaining_balance,
            'ownership_proof_url' => $this->ownership_proof_url,
            'maincourts_count' => $this->maincourts_count ?? ($this->relationLoaded('maincourts') ? $this->maincourts->count() : null),
            'maincourts' => $this->whenLoaded('maincourts', function () {
                return $this->maincourts->map(function ($maincourt) {
                    return [
                        'id' => $maincourt->id,
                        'name' => $maincourt->name,
                        'status' => $maincourt->status,
                        'is_verified' => $maincourt->is_verified,
                        'courts_count' => $maincourt->courts_count ?? ($maincourt->relationLoaded('courts') ? $maincourt->courts->count() : null),
                    ];
                });
            }),
            'payments' => $this->whenLoaded('payments', function () {
                return OwnerPaymentResource::collection($this->payments);
            }),
        ];
    }
}
