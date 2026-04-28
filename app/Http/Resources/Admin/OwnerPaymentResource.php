<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class OwnerPaymentResource extends JsonResource
{
    public function toArray($request): array
    {
        $owner = $this->whenLoaded('owner');
        $ownerUser = $owner?->user;

        return [
            'id' => $this->id,
            'owner_id' => $this->owner_id,
            'amount' => $this->amount,
            'payment_type' => $this->payment_type,
            'receipt_image_url' => $this->receipt_image_url,
            'notes' => $this->notes,
            'status' => $this->status,
            'rejection_reason' => $this->rejection_reason,
            'created_at' => $this->created_at,
            'owner' => $ownerUser ? [
                'id' => $ownerUser->id,
                'name' => $ownerUser->name,
                'email' => $ownerUser->email,
                'app_due_amount' => $owner?->app_due_amount,
            ] : null,
        ];
    }
}
