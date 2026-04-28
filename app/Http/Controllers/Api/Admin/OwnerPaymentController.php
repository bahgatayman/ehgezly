<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\ApprovePaymentRequest;
use App\Http\Requests\Admin\RejectPaymentRequest;
use App\Http\Resources\Admin\OwnerPaymentResource;
use App\Models\Courtowner;
use App\Models\Notification;
use App\Models\OwnerPayment;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class OwnerPaymentController extends Controller
{
    public function index(): JsonResponse
    {
        $query = OwnerPayment::with(['owner.user'])
            ->orderByDesc('created_at');

        $status = request()->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $ownerId = request()->query('owner_id');
        if ($ownerId) {
            $query->where('owner_id', $ownerId);
        }

        $payments = $query->get();

        return $this->successResponse('Owner payments retrieved.', OwnerPaymentResource::collection($payments));
    }

    public function show(int $id): JsonResponse
    {
        $payment = OwnerPayment::with(['owner.user'])->find($id);
        if (!$payment) {
            return $this->errorResponse('Payment not found.', 404);
        }

        return $this->successResponse('Payment retrieved.', new OwnerPaymentResource($payment));
    }

    public function approve(ApprovePaymentRequest $request, int $id): JsonResponse
    {
        $result = DB::transaction(function () use ($id) {
            $payment = OwnerPayment::with(['owner.user'])->lockForUpdate()->find($id);
            if (!$payment) {
                return ['error' => ['message' => 'Payment not found.', 'status' => 404]];
            }

            if ($payment->status !== 'pending') {
                return ['error' => ['message' => 'Wrong status.', 'status' => 409]];
            }

            $payment->update(['status' => 'approved']);

            $owner = Courtowner::lockForUpdate()->find($payment->owner_id);
            if ($owner) {
                $newDue = $owner->app_due_amount - $payment->amount;
                $owner->update(['app_due_amount' => max(0, $newDue)]);
            }

            if ($payment->owner && $payment->owner->user) {
                Notification::create([
                    'user_id' => $payment->owner->user->id,
                    'title' => 'تم قبول دفعتك',
                    'message' => "تم قبول دفعتك بمبلغ {$payment->amount} جنيه وتم خصمه من المستحقات",
                    'type' => 'payment_approved',
                    'notifiable_type' => OwnerPayment::class,
                    'notifiable_id' => $payment->id,
                    'is_read' => false,
                ]);
            }

            $adminId = auth()->id();
            if ($adminId) {
                Notification::where('user_id', $adminId)
                    ->where('type', 'owner_payment')
                    ->where('notifiable_id', $payment->id)
                    ->update(['is_read' => true]);
            }

            $payment->load(['owner.user']);

            return ['payment' => $payment];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Payment approved.', new OwnerPaymentResource($result['payment']));
    }

    public function reject(RejectPaymentRequest $request, int $id): JsonResponse
    {
        $reason = $request->validated()['rejection_reason'];

        $result = DB::transaction(function () use ($id, $reason) {
            $payment = OwnerPayment::with(['owner.user'])->lockForUpdate()->find($id);
            if (!$payment) {
                return ['error' => ['message' => 'Payment not found.', 'status' => 404]];
            }

            if ($payment->status !== 'pending') {
                return ['error' => ['message' => 'Wrong status.', 'status' => 409]];
            }

            $payment->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            if ($payment->owner && $payment->owner->user) {
                Notification::create([
                    'user_id' => $payment->owner->user->id,
                    'title' => 'تم رفض دفعتك',
                    'message' => "تم رفض دفعتك بمبلغ {$payment->amount} جنيه - السبب: {$reason}",
                    'type' => 'payment_rejected',
                    'notifiable_type' => OwnerPayment::class,
                    'notifiable_id' => $payment->id,
                    'is_read' => false,
                ]);
            }

            $payment->load(['owner.user']);

            return ['payment' => $payment];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Payment rejected.', new OwnerPaymentResource($result['payment']));
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
