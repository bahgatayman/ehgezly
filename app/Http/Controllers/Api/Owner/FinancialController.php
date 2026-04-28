<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Resources\Owner\FinancialResource;
use App\Models\Courtowner;
use App\Models\OwnerPayment;
use Illuminate\Http\JsonResponse;

class FinancialController extends Controller
{
    public function index(): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $paymentHistory = OwnerPayment::where('owner_id', $owner->id)
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $totalPaid = OwnerPayment::where('owner_id', $owner->id)
            ->where('status', 'approved')
            ->sum('amount');

        $pendingPayments = OwnerPayment::where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->count();

        $data = [
            'commission_percentage' => $owner->commission_percentage,
            'total_revenue' => $owner->total_revenue,
            'app_due_amount' => $owner->app_due_amount,
            'remaining_balance' => $owner->remaining_balance,
            'total_paid' => $totalPaid,
            'pending_payments' => $pendingPayments,
            'payment_history' => $paymentHistory,
        ];

        return $this->successResponse('Financial overview retrieved.', new FinancialResource($data));
    }

    private function getOwner(): ?Courtowner
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Courtowner::where('user_id', $user->id)->first();
    }

    private function successResponse(string $message, $data = null, int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    private function errorResponse(string $message, int $status): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'data' => null,
        ], $status);
    }
}
