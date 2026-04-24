<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StorePaymentMethodRequest;
use App\Http\Resources\PaymentMethodResource;
use App\Models\Courtowner;
use App\Models\Maincourt;
use App\Models\PaymentMethod;
use Illuminate\Http\JsonResponse;

class PaymentMethodController extends Controller
{
    public function index(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($id);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $methods = $maincourt->paymentMethods()->get();

        return $this->successResponse('Payment methods retrieved.', PaymentMethodResource::collection($methods));
    }

    public function store(StorePaymentMethodRequest $request, int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($id);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $data = $request->validated();
        $data['maincourt_id'] = $maincourt->id;

        $method = PaymentMethod::create($data);

        return $this->successResponse('Payment method created.', new PaymentMethodResource($method), 201);
    }

    public function update(StorePaymentMethodRequest $request, int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $method = PaymentMethod::find($id);
        if (!$method) {
            return $this->errorResponse('Payment method not found.', 404);
        }

        $method->load('maincourt');
        if (!$method->maincourt || $method->maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $method->update($request->validated());

        return $this->successResponse('Payment method updated.', new PaymentMethodResource($method));
    }

    public function destroy(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $method = PaymentMethod::find($id);
        if (!$method) {
            return $this->errorResponse('Payment method not found.', 404);
        }

        $method->load('maincourt');
        if (!$method->maincourt || $method->maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $method->delete();

        return $this->successResponse('Payment method deleted.', null);
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
