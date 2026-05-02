<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\StoreBookingRequest;
use App\Http\Resources\Customer\BookingResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Customer;
use App\Models\OpenMatch;
use App\Models\Notification;
use App\Models\PaymentMethod;
use App\Models\Timeslot;
use App\Traits\HandlesFileUpload;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class BookingController extends Controller
{
    use HandlesFileUpload;
    public function store(StoreBookingRequest $request): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if (!$customer->can_book) {
            return $this->errorResponse('Cannot book.', 403);
        }

        $data = $request->validated();

        $court = Court::with('maincourt.owner.user')->find($data['court_id']);
        if (!$court || $court->status !== 'open' || !$court->is_open) {
            return $this->errorResponse('Court not available.', 409);
        }

        $paymentMethod = PaymentMethod::find($data['payment_method_id']);
        if (!$paymentMethod || $paymentMethod->maincourt_id !== $court->maincourt_id) {
            return $this->errorResponse('Payment method invalid.', 409);
        }

        $receiptFile = $request->file('receipt_image');

        $result = DB::transaction(function () use ($data, $court, $customer, $paymentMethod, $receiptFile) {
            $timeslot = Timeslot::where('id', $data['timeslot_id'])
                ->lockForUpdate()
                ->first();

            if (!$timeslot || $timeslot->court_id !== $court->id) {
                return ['error' => ['message' => 'Timeslot not available.', 'status' => 409]];
            }

            if (!in_array($timeslot->status, ['available', 'pending_match'], true)) {
                return ['error' => ['message' => 'Timeslot not available.', 'status' => 409]];
            }

            $slotDate = Carbon::parse($timeslot->date)->startOfDay();
            if ($slotDate->lt(Carbon::today())) {
                return ['error' => ['message' => 'Timeslot not available.', 'status' => 409]];
            }

            $url = $this->uploadFile($receiptFile, "receipts/{$customer->id}");

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'court_id' => $court->id,
                'timeslot_id' => $timeslot->id,
                'payment_method_id' => $paymentMethod->id,
                'total_price' => $court->price_per_hour,
                'receipt_image_url' => $url,
                'status' => 'pending',
            ]);

            $timeslot->update(['status' => 'booked']);

            $openMatch = OpenMatch::with(['players.customer.user'])
                ->where('timeslot_id', $timeslot->id)
                ->whereIn('status', ['waiting_players', 'ready_to_book'])
                ->lockForUpdate()
                ->first();

            if ($openMatch) {
                $openMatch->update(['status' => 'cancelled']);

                foreach ($openMatch->players as $player) {
                    if ($player->customer?->user) {
                        Notification::create([
                            'user_id' => $player->customer->user->id,
                            'title' => 'تم إلغاء الماتش',
                            'message' => "تم حجز الملعب من شخص آخر، تم إلغاء ماتش {$openMatch->name}",
                            'type' => 'match_cancelled_by_booking',
                            'notifiable_type' => OpenMatch::class,
                            'notifiable_id' => $openMatch->id,
                            'is_read' => false,
                        ]);
                    }
                }
            }

            $ownerUserId = $court->maincourt?->owner?->user?->id;
            if ($ownerUserId) {
                Notification::create([
                    'user_id' => $ownerUserId,
                    'title' => 'حجز جديد',
                    'message' => "لديك حجز جديد على {$court->name} بتاريخ {$timeslot->date} من {$timeslot->start_time} إلى {$timeslot->end_time}",
                    'type' => 'new_booking',
                    'notifiable_type' => Booking::class,
                    'notifiable_id' => $booking->id,
                    'is_read' => false,
                ]);
            }

            return ['booking' => $booking];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        $booking = $result['booking'];
        $booking->load(['court.maincourt', 'timeslot', 'paymentMethod']);

        return $this->successResponse('Booking created.', new BookingResource($booking), 201);
    }

    public function index(): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $query = Booking::where('customer_id', $customer->id)
            ->with([
                'court.maincourt.workingHours',
                'court.maincourt.paymentMethods',
                'timeslot',
                'paymentMethod',
            ])
            ->orderByDesc('created_at');

        $status = request()->query('status');
        if ($status) {
            $query->where('status', $status);
        }

        $bookings = $query->get();

        return $this->successResponse('حجوزاتك', [
            'stats' => [
                'booking_count' => $customer->bookingCount(),
                'active_bookings_count' => $customer->activeBookingsCount(),
                'completed_bookings_count' => $customer->completedBookingsCount(),
                'cancelled_bookings_count' => $customer->cancelledBookingsCount(),
                'rejected_bookings_count' => $customer->rejectedBookingsCount(),
            ],
            'bookings' => BookingResource::collection($bookings),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $booking = Booking::with([
                'court.maincourt.images',
                'court.maincourt.workingHours',
                'court.maincourt.paymentMethods',
                'timeslot',
                'paymentMethod',
            ])
            ->find($id);

        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->customer_id !== $customer->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        return $this->successResponse('Booking retrieved.', new BookingResource($booking));
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $booking = Booking::with(['court.maincourt.owner.user', 'timeslot'])->find($id);
        if (!$booking) {
            return $this->errorResponse('Booking not found.', 404);
        }

        if ($booking->customer_id !== $customer->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($booking->status === 'confirmed') {
            return $this->errorResponse('Cannot cancel confirmed booking.', 409);
        }

        if ($booking->status !== 'pending') {
            return $this->errorResponse('Cannot cancel booking.', 409);
        }

        DB::transaction(function () use ($booking) {
            $booking->update(['status' => 'cancelled']);
            if ($booking->timeslot) {
                $booking->timeslot->update(['status' => 'available']);
            }

            $ownerUserId = $booking->court?->maincourt?->owner?->user?->id;
            if ($ownerUserId) {
                Notification::create([
                    'user_id' => $ownerUserId,
                    'title' => 'تم إلغاء الحجز',
                    'message' => "تم إلغاء الحجز على {$booking->court->name} بتاريخ {$booking->timeslot->date}",
                    'type' => 'booking_cancelled',
                    'notifiable_type' => Booking::class,
                    'notifiable_id' => $booking->id,
                    'is_read' => false,
                ]);
            }
        });

        return $this->successResponse('Booking cancelled.', null);
    }

    private function getCustomer(): ?Customer
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Customer::where('user_id', $user->id)->first();
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
