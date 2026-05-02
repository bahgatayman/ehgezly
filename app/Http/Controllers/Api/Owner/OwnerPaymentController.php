<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreOwnerPaymentRequest;
use App\Http\Resources\Owner\OwnerPaymentResource;
use App\Models\Courtowner;
use App\Models\Notification;
use App\Models\OwnerPayment;
use App\Models\User;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;

class OwnerPaymentController extends Controller
{
    use HandlesFileUpload;
    private const APP_PAYMENT_METHODS = [
        [
            'type' => 'instapay',
            'identifier' => '01000000000',
            'name' => 'InstaPay',
            'instructions' => 'قم بالتحويل على رقم InstaPay وارفع صورة الإيصال',
        ],
        [
            'type' => 'vodafone_cash',
            'identifier' => '01000000000',
            'name' => 'Vodafone Cash',
            'instructions' => 'قم بالتحويل على رقم Vodafone Cash وارفع صورة الإيصال',
        ],
        [
            'type' => 'etisalat_cash',
            'identifier' => '01000000000',
            'name' => 'Etisalat Cash',
            'instructions' => 'قم بالتحويل على رقم Etisalat Cash وارفع صورة الإيصال',
        ],
        [
            'type' => 'orange_cash',
            'identifier' => '01000000000',
            'name' => 'Orange Cash',
            'instructions' => 'قم بالتحويل على رقم Orange Cash وارفع صورة الإيصال',
        ],
    ];

    public function appPaymentInfo(): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        return $this->successResponse('بيانات الدفع للتطبيق', [
            'app_due_amount' => $owner->app_due_amount,
            'payment_methods' => self::APP_PAYMENT_METHODS,
        ]);
    }

    public function index(): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $query = OwnerPayment::where('owner_id', $owner->id)
            ->orderByDesc('created_at');

        $status = request()->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $payments = $query->get();

        return $this->successResponse('Payments retrieved.', OwnerPaymentResource::collection($payments));
    }

    public function store(StoreOwnerPaymentRequest $request): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($owner->app_due_amount <= 0) {
            return $this->errorResponse('ليس لديك مستحقات للتطبيق حالياً', 409);
        }

        $hasPending = OwnerPayment::where('owner_id', $owner->id)
            ->where('status', 'pending')
            ->exists();

        if ($hasPending) {
            return $this->errorResponse('لديك دفعة قيد المراجعة، يرجى انتظار مراجعتها أولاً', 409);
        }

        $data = $request->validated();
        if ($data['amount'] > $owner->app_due_amount) {
            return $this->errorResponse('المبلغ المدخل أكبر من المستحقات عليك', 422);
        }

        $receiptUrl = $this->uploadFile(
            $request->file('receipt_image'),
            "owner_payments/{$owner->id}"
        );

        $payment = OwnerPayment::create([
            'owner_id' => $owner->id,
            'amount' => $data['amount'],
            'payment_type' => $data['payment_type'],
            'receipt_image_url' => $receiptUrl,
            'notes' => $data['notes'] ?? null,
            'status' => 'pending',
        ]);

        $owner->load('user');
        $admins = User::where('role', 'admin')->get();
        foreach ($admins as $admin) {
            Notification::create([
                'user_id' => $admin->id,
                'title' => 'دفعة جديدة من مالك ملعب',
                'message' => "قام {$owner->user->name} بإرسال دفعة بمبلغ {$payment->amount} جنيه",
                'type' => 'owner_payment',
                'notifiable_type' => OwnerPayment::class,
                'notifiable_id' => $payment->id,
                'is_read' => false,
            ]);
        }

        return $this->successResponse('Payment submitted.', new OwnerPaymentResource($payment), 201);
    }

    public function show(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $payment = OwnerPayment::find($id);
        if (!$payment) {
            return $this->errorResponse('Payment not found.', 404);
        }

        if ($payment->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        return $this->successResponse('Payment retrieved.', new OwnerPaymentResource($payment));
    }

    public function destroy(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $payment = OwnerPayment::find($id);
        if (!$payment) {
            return $this->errorResponse('Payment not found.', 404);
        }

        if ($payment->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($payment->status !== 'pending') {
            return $this->errorResponse('لا يمكن حذف دفعة تمت مراجعتها', 409);
        }

        $this->deleteReceiptFile($payment->receipt_image_url);
        $payment->delete();

        return $this->successResponse('Payment deleted.', null);
    }

    private function getOwner(): ?Courtowner
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Courtowner::where('user_id', $user->id)->first();
    }

    private function deleteReceiptFile(string $url): void
    {
        $this->deleteFile($url);
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
