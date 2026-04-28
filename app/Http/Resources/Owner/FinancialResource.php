<?php

namespace App\Http\Resources\Owner;

use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Collection;

class FinancialResource extends JsonResource
{
    public function toArray($request): array
    {
        $history = $this['payment_history'] ?? collect();
        if (!$history instanceof Collection) {
            $history = collect($history);
        }

        return [
            'commission_percentage' => $this['commission_percentage'],
            'total_revenue' => $this['total_revenue'],
            'app_due_amount' => $this['app_due_amount'],
            'remaining_balance' => $this['remaining_balance'],
            'total_paid' => $this['total_paid'],
            'pending_payments' => $this['pending_payments'],
            'payment_history' => $history->map(function ($payment) {
                return [
                    'id' => $payment->id,
                    'amount' => $payment->amount,
                    'payment_type' => $payment->payment_type,
                    'status' => $payment->status,
                    'rejection_reason' => $payment->rejection_reason,
                    'created_at' => $payment->created_at,
                ];
            })->values(),
        ];
    }
}
