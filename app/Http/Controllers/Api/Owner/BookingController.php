<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\RejectBookingRequest;
use App\Http\Resources\Owner\BookingResource;
use App\Models\Booking;
use App\Models\Courtowner;
use App\Models\Notification;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    public function index(): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $query = Booking::whereHas('court.maincourt', function ($q) use ($owner) {
                $q->where('owner_id', $owner->id);
            })
            ->with(['customer.user', 'court.maincourt', 'timeslot', 'paymentMethod'])
            ->orderByDesc('created_at');

        $status = request()->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $courtId = request()->query('court_id');
        if ($courtId) {
            $query->where('court_id', $courtId);
        }

        $maincourtId = request()->query('maincourt_id');
        if ($maincourtId) {
            $query->whereHas('court', function ($q) use ($maincourtId) {
                $q->where('maincourt_id', $maincourtId);
            });
        }

        $date = request()->query('date');
        if ($date) {
            $query->whereHas('timeslot', function ($q) use ($date) {
                $q->where('date', $date);
            });
        }

        $bookings = $query->get();

        return $this->successResponse('Bookings retrieved.', BookingResource::collection($bookings));
    }

    public function show(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $booking = Booking::with(['customer.user', 'court.maincourt', 'timeslot', 'paymentMethod'])
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if (!$this->belongsToOwner($booking, $owner->id)) {
            return $this->errorResponse('Forbidden.', 403);
        }

        return $this->successResponse('Booking retrieved.', new BookingResource($booking));
    }

    public function confirm(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $result = DB::transaction(function () use ($id, $owner) {
            $booking = Booking::with(['customer.user', 'court.maincourt', 'timeslot', 'paymentMethod'])
                ->lockForUpdate()
                ->find($id);

            if (!$booking) {
                return ['error' => ['message' => 'Booking not found.', 'status' => 404]];
            }

            if (!$this->belongsToOwner($booking, $owner->id)) {
                return ['error' => ['message' => 'Forbidden.', 'status' => 403]];
            }

            if ($booking->status !== 'pending') {
                return ['error' => ['message' => 'Wrong status.', 'status' => 409]];
            }

            $slotDate = Carbon::parse($booking->timeslot->date)->startOfDay();
            if ($slotDate->lt(Carbon::today())) {
                return ['error' => ['message' => 'Timeslot in the past.', 'status' => 409]];
            }

            $booking->update(['status' => 'confirmed']);
            $booking->timeslot->update(['status' => 'booked']);

            $commission = round($booking->total_price * 0.05, 2);
            $ownerShare = round($booking->total_price * 0.95, 2);

            $owner->update([
                'total_revenue' => $owner->total_revenue + $booking->total_price,
                'app_due_amount' => $owner->app_due_amount + $commission,
                'remaining_balance' => $owner->remaining_balance + $ownerShare,
            ]);

            Notification::create([
                'user_id' => $booking->customer->user_id,
                'title' => 'تم تأكيد حجزك',
                'message' => "تم تأكيد حجزك على {$booking->court->name} بتاريخ {$booking->timeslot->date} من {$booking->timeslot->start_time} إلى {$booking->timeslot->end_time}",
                'type' => 'booking_confirmed',
                'notifiable_type' => Booking::class,
                'notifiable_id' => $booking->id,
                'is_read' => false,
            ]);

            return ['booking' => $booking];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Booking confirmed.', new BookingResource($result['booking']));
    }

    public function reject(RejectBookingRequest $request, int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $reason = $request->validated()['rejection_reason'];

        $result = DB::transaction(function () use ($id, $owner, $reason) {
            $booking = Booking::with(['customer.user', 'court.maincourt', 'timeslot', 'paymentMethod'])
                ->lockForUpdate()
                ->find($id);

            if (!$booking) {
                return ['error' => ['message' => 'Booking not found.', 'status' => 404]];
            }

            if (!$this->belongsToOwner($booking, $owner->id)) {
                return ['error' => ['message' => 'Forbidden.', 'status' => 403]];
            }

            if ($booking->status !== 'pending') {
                return ['error' => ['message' => 'Wrong status.', 'status' => 409]];
            }

            $booking->update([
                'status' => 'rejected',
                'rejection_reason' => $reason,
            ]);

            $booking->timeslot->update(['status' => 'available']);

            Notification::create([
                'user_id' => $booking->customer->user_id,
                'title' => 'تم رفض حجزك',
                'message' => "تم رفض حجزك على {$booking->court->name} بتاريخ {$booking->timeslot->date} - السبب: {$reason}",
                'type' => 'booking_rejected',
                'notifiable_type' => Booking::class,
                'notifiable_id' => $booking->id,
                'is_read' => false,
            ]);

            return ['booking' => $booking];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Booking rejected.', new BookingResource($result['booking']));
    }

    public function complete(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $booking = Booking::with(['customer.user', 'court.maincourt', 'timeslot', 'paymentMethod'])
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if (!$this->belongsToOwner($booking, $owner->id)) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($booking->status !== 'confirmed') {
            return $this->errorResponse('Wrong status.', 409);
        }

        $slotDate = Carbon::parse($booking->timeslot->date)->startOfDay();
        if ($slotDate->gt(Carbon::today())) {
            return $this->errorResponse('Timeslot in the future.', 409);
        }

        $booking->update(['status' => 'completed']);

        return $this->successResponse('Booking completed.', new BookingResource($booking));
    }

    private function belongsToOwner(Booking $booking, int $ownerId): bool
    {
        return $booking->court
            && $booking->court->maincourt
            && $booking->court->maincourt->owner_id === $ownerId;
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
