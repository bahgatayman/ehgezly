<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\SuspendMaincourtRequest;
use App\Http\Resources\Admin\MaincourtResource;
use App\Models\Maincourt;
use App\Models\Notification;
use Illuminate\Http\JsonResponse;

class MaincourtController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Maincourt::with(['owner.user', 'courts'])
            ->withCount('courts')
            ->orderByDesc('created_at');

        $isVerified = request()->query('is_verified');
        if (!is_null($isVerified)) {
            $query->where('is_verified', filter_var($isVerified, FILTER_VALIDATE_BOOLEAN));
        }

        $status = request()->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $search = request()->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $maincourts = $query->get();

        return $this->successResponse('Maincourts retrieved.', MaincourtResource::collection($maincourts));
    }

    public function show(int $id): JsonResponse
    {
        $maincourt = Maincourt::with([
                'owner.user',
                'courts',
                'images',
                'amenities',
                'workingHours',
                'paymentMethods',
            ])
            ->withCount('courts')
            ->find($id);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        return $this->successResponse('Maincourt retrieved.', new MaincourtResource($maincourt));
    }

    public function verify(int $id): JsonResponse
    {
        $maincourt = Maincourt::with('owner.user')->find($id);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->is_verified) {
            return $this->errorResponse('Wrong status.', 409);
        }

        $maincourt->update([
            'is_verified' => true,
            'status' => 'active',
        ]);

        $ownerUserId = $maincourt->owner?->user_id;
        if ($ownerUserId) {
            Notification::create([
                'user_id' => $ownerUserId,
                'title' => 'تم التحقق من ملعبك',
                'message' => "تم التحقق من ملعب {$maincourt->name} وهو الآن متاح للحجز",
                'type' => 'maincourt_verified',
                'notifiable_type' => Maincourt::class,
                'notifiable_id' => $maincourt->id,
                'is_read' => false,
            ]);
        }

        $maincourt->load(['owner.user', 'courts']);

        return $this->successResponse('Maincourt verified.', new MaincourtResource($maincourt));
    }

    public function suspend(SuspendMaincourtRequest $request, int $id): JsonResponse
    {
        $reason = $request->validated()['suspension_reason'];

        $maincourt = Maincourt::with('owner.user')->find($id);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        $maincourt->update(['status' => 'suspended']);

        $ownerUserId = $maincourt->owner?->user_id;
        if ($ownerUserId) {
            Notification::create([
                'user_id' => $ownerUserId,
                'title' => 'تم تعليق ملعبك',
                'message' => "تم تعليق ملعب {$maincourt->name} - السبب: {$reason}",
                'type' => 'maincourt_suspended',
                'notifiable_type' => Maincourt::class,
                'notifiable_id' => $maincourt->id,
                'is_read' => false,
            ]);
        }

        $maincourt->load(['owner.user', 'courts']);

        return $this->successResponse('Maincourt suspended.', new MaincourtResource($maincourt));
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
