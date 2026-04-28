<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\RejectOwnerRequest;
use App\Http\Requests\Admin\SuspendOwnerRequest;
use App\Http\Requests\Admin\UpdateCommissionRequest;
use App\Http\Resources\Admin\OwnerResource;
use App\Mail\OwnerApprovedMail;
use App\Mail\OwnerRejectedMail;
use App\Models\Courtowner;
use App\Models\Notification;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class OwnerController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Courtowner::with(['user', 'maincourts'])
            ->withCount('maincourts')
            ->orderByDesc('created_at');

        $status = request()->query('status');
        if ($status) {
            $query->whereHas('user', function ($q) use ($status) {
                $q->where('status', $status);
            });
        }

        $search = request()->query('search');
        if ($search) {
            $query->whereHas('user', function ($q) use ($search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('phone', 'like', "%{$search}%");
                });
            });
        }

        $owners = $query->get();

        return $this->successResponse('Owners retrieved.', OwnerResource::collection($owners));
    }

    public function show(int $id): JsonResponse
    {
        $owner = Courtowner::with([
                'user',
                'maincourts' => function ($query) {
                    $query->withCount('courts');
                },
                'payments',
            ])
            ->find($id);

        if (!$owner) {
            return $this->errorResponse('Owner not found.', 404);
        }

        return $this->successResponse('Owner retrieved.', new OwnerResource($owner));
    }

    public function approve(int $id): JsonResponse
    {
        $result = DB::transaction(function () use ($id) {
            $owner = Courtowner::with('user')->lockForUpdate()->find($id);
            if (!$owner) {
                return ['error' => ['message' => 'Owner not found.', 'status' => 404]];
            }

            $user = $owner->user;
            if (!$user) {
                return ['error' => ['message' => 'User not found.', 'status' => 404]];
            }

            if ($user->status !== 'pending') {
                return ['error' => ['message' => 'Wrong status.', 'status' => 409]];
            }

            $user->update(['status' => 'active']);

            Mail::to($user->email)->send(new OwnerApprovedMail($user));

            Notification::create([
                'user_id' => $user->id,
                'title' => 'تم قبول طلبك',
                'message' => 'تم قبول طلبك كمالك ملعب في منصة إهجزلي، يمكنك الآن إضافة ملاعبك',
                'type' => 'owner_approved',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'is_read' => false,
            ]);

            $adminId = auth()->id();
            if ($adminId) {
                Notification::where('user_id', $adminId)
                    ->where('type', 'new_owner_request')
                    ->where('notifiable_id', $user->id)
                    ->update(['is_read' => true]);
            }

            $owner->load(['user', 'maincourts', 'payments']);

            return ['owner' => $owner];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Owner approved.', new OwnerResource($result['owner']));
    }

    public function reject(RejectOwnerRequest $request, int $id): JsonResponse
    {
        $reason = $request->validated()['rejection_reason'];

        $result = DB::transaction(function () use ($id, $reason) {
            $owner = Courtowner::with('user')->lockForUpdate()->find($id);
            if (!$owner) {
                return ['error' => ['message' => 'Owner not found.', 'status' => 404]];
            }

            $user = $owner->user;
            if (!$user) {
                return ['error' => ['message' => 'User not found.', 'status' => 404]];
            }

            if ($user->status !== 'pending') {
                return ['error' => ['message' => 'Wrong status.', 'status' => 409]];
            }

            $user->update(['status' => 'rejected']);

            Mail::to($user->email)->send(new OwnerRejectedMail($user, $reason));

            Notification::create([
                'user_id' => $user->id,
                'title' => 'تم رفض طلبك',
                'message' => "تم رفض طلبك كمالك ملعب - السبب: {$reason}",
                'type' => 'owner_rejected',
                'notifiable_type' => User::class,
                'notifiable_id' => $user->id,
                'is_read' => false,
            ]);

            $adminId = auth()->id();
            if ($adminId) {
                Notification::where('user_id', $adminId)
                    ->where('type', 'new_owner_request')
                    ->where('notifiable_id', $user->id)
                    ->update(['is_read' => true]);
            }

            $owner->load(['user', 'maincourts', 'payments']);

            return ['owner' => $owner];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Owner rejected.', new OwnerResource($result['owner']));
    }

    public function suspend(SuspendOwnerRequest $request, int $id): JsonResponse
    {
        $reason = $request->validated()['suspension_reason'];

        $owner = Courtowner::with('user')->find($id);
        if (!$owner) {
            return $this->errorResponse('Owner not found.', 404);
        }

        $user = $owner->user;
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->status !== 'active') {
            return $this->errorResponse('Wrong status.', 409);
        }

        $user->update(['status' => 'suspended']);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'تم تعليق حسابك',
            'message' => "تم تعليق حسابك - السبب: {$reason}",
            'type' => 'account_suspended',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'is_read' => false,
        ]);

        $owner->load(['user', 'maincourts', 'payments']);

        return $this->successResponse('Owner suspended.', new OwnerResource($owner));
    }

    public function activate(int $id): JsonResponse
    {
        $owner = Courtowner::with('user')->find($id);
        if (!$owner) {
            return $this->errorResponse('Owner not found.', 404);
        }

        $user = $owner->user;
        if (!$user) {
            return $this->errorResponse('User not found.', 404);
        }

        if ($user->status !== 'suspended') {
            return $this->errorResponse('Wrong status.', 409);
        }

        $user->update(['status' => 'active']);

        Notification::create([
            'user_id' => $user->id,
            'title' => 'تم تفعيل حسابك',
            'message' => 'تم تفعيل حسابك، يمكنك الآن إضافة ملاعبك',
            'type' => 'account_activated',
            'notifiable_type' => User::class,
            'notifiable_id' => $user->id,
            'is_read' => false,
        ]);

        $owner->load(['user', 'maincourts', 'payments']);

        return $this->successResponse('Owner activated.', new OwnerResource($owner));
    }

    public function updateCommission(UpdateCommissionRequest $request, int $id): JsonResponse
    {
        $commission = $request->validated()['commission_percentage'];

        $owner = Courtowner::with('user')->find($id);
        if (!$owner) {
            return $this->errorResponse('Owner not found.', 404);
        }

        $owner->update(['commission_percentage' => $commission]);

        $userId = $owner->user_id;
        Notification::create([
            'user_id' => $userId,
            'title' => 'تم تحديث نسبة العمولة',
            'message' => "تم تحديث نسبة عمولتك إلى {$commission}%",
            'type' => 'commission_updated',
            'notifiable_type' => Courtowner::class,
            'notifiable_id' => $owner->id,
            'is_read' => false,
        ]);

        $owner->load(['user', 'maincourts', 'payments']);

        return $this->successResponse('Commission updated.', new OwnerResource($owner));
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
