<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Requests\Customer\PayMatchRequest;
use App\Http\Requests\Customer\StoreOpenMatchRequest;
use App\Http\Resources\Customer\BookingResource;
use App\Http\Resources\Customer\OpenMatchResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Customer;
use App\Models\MatchPlayer;
use App\Models\Notification;
use App\Models\OpenMatch;
use App\Models\PaymentMethod;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class OpenMatchController extends Controller
{
    public function index(): JsonResponse
    {
        $query = OpenMatch::whereIn('status', ['waiting_players', 'ready_to_book'])
            ->with(['court.maincourt.primaryImage', 'timeslot', 'creator.user'])
            ->orderByDesc('created_at');

        $courtId = request()->query('court_id');
        if ($courtId) {
            $query->where('court_id', $courtId);
        }

        $date = request()->query('date');
        if ($date) {
            $query->whereHas('timeslot', function ($q) use ($date) {
                $q->where('date', $date);
            });
        }

        $matches = $query->get();

        return $this->successResponse('Matches retrieved.', OpenMatchResource::collection($matches));
    }

    public function show(int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $match = OpenMatch::with([
                'court.maincourt',
                'timeslot',
                'creator.user',
                'joinedPlayers.customer.user',
            ])
            ->find($id);

        if (!$match) {
            return $this->errorResponse('Match not found.', 404);
        }

        $authStatus = 'none';
        if ($match->creator_id === $customer->id) {
            $authStatus = 'creator';
        } else {
            $player = MatchPlayer::where('match_id', $match->id)
                ->where('customer_id', $customer->id)
                ->first();

            if ($player) {
                $authStatus = $player->status === 'waitlisted' ? 'waitlisted' : 'joined';
            }
        }

        $match->setAttribute('auth_status', $authStatus);

        return $this->successResponse('Match retrieved.', new OpenMatchResource($match));
    }

    public function store(StoreOpenMatchRequest $request): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if (!$customer->can_book) {
            return $this->errorResponse('أنت محظور من الحجز', 403);
        }

        $data = $request->validated();

        $court = Court::find($data['court_id']);
        if (!$court || $court->status !== 'open' || !$court->is_open) {
            return $this->errorResponse('الملعب غير متاح حالياً', 409);
        }

        $result = DB::transaction(function () use ($data, $customer, $court) {
            $timeslot = Timeslot::where('id', $data['timeslot_id'])
                ->lockForUpdate()
                ->first();

            if (!$timeslot || $timeslot->court_id !== $court->id) {
                return ['error' => ['message' => 'الميعاد لا ينتمي للملعب المختار', 'status' => 422]];
            }

            if ($timeslot->status !== 'available') {
                return ['error' => ['message' => 'هذا الميعاد غير متاح', 'status' => 409]];
            }

            $slotDate = Carbon::parse($timeslot->date)->startOfDay();
            if ($slotDate->lt(Carbon::today())) {
                return ['error' => ['message' => 'لا يمكن فتح ماتش في ميعاد منتهي', 'status' => 422]];
            }

            $hasActiveMatch = OpenMatch::where('creator_id', $customer->id)
                ->where('timeslot_id', $timeslot->id)
                ->whereIn('status', ['waiting_players', 'ready_to_book', 'booking_pending', 'confirmed'])
                ->exists();

            if ($hasActiveMatch) {
                return ['error' => ['message' => 'لديك ماتش مفتوح على هذا الميعاد بالفعل', 'status' => 409]];
            }

            $timeslot->update(['status' => 'pending_match']);

            $match = OpenMatch::create([
                'court_id' => $court->id,
                'timeslot_id' => $timeslot->id,
                'creator_id' => $customer->id,
                'name' => $data['name'],
                'description' => $data['description'] ?? null,
                'required_players' => $data['required_players'],
                'current_players' => 1,
                'status' => 'waiting_players',
            ]);

            MatchPlayer::create([
                'match_id' => $match->id,
                'customer_id' => $customer->id,
                'status' => 'joined',
                'joined_at' => now(),
            ]);

            $match->load(['court.maincourt.primaryImage', 'timeslot', 'creator.user']);

            return ['match' => $match];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse('Match created.', new OpenMatchResource($result['match']), 201);
    }

    public function join(int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $result = DB::transaction(function () use ($id, $customer) {
            $match = OpenMatch::with(['timeslot', 'creator.user'])
                ->lockForUpdate()
                ->find($id);

            if (!$match) {
                return ['error' => ['message' => 'Match not found.', 'status' => 404]];
            }

            if ($match->status !== 'waiting_players') {
                return ['error' => ['message' => 'لا يمكن الانضمام لهذا الماتش', 'status' => 409]];
            }

            if ($match->creator_id === $customer->id) {
                return ['error' => ['message' => 'أنت صاحب هذا الماتش', 'status' => 409]];
            }

            $slotDate = Carbon::parse($match->timeslot->date)->startOfDay();
            if ($slotDate->lt(Carbon::today())) {
                return ['error' => ['message' => 'انتهى وقت هذا الماتش', 'status' => 409]];
            }

            $existing = MatchPlayer::where('match_id', $match->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['joined', 'waitlisted'])
                ->first();

            if ($existing) {
                return ['error' => ['message' => 'أنت بالفعل في هذا الماتش', 'status' => 409]];
            }

            if (!$match->isFull()) {
                MatchPlayer::create([
                    'match_id' => $match->id,
                    'customer_id' => $customer->id,
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);

                $match->update(['current_players' => $match->current_players + 1]);

                if ($match->isFull()) {
                    $match->update(['status' => 'ready_to_book']);

                    if ($match->creator?->user) {
                        Notification::create([
                            'user_id' => $match->creator->user->id,
                            'title' => 'اكتمل العدد!',
                            'message' => "اكتمل عدد لاعبي ماتش {$match->name}، يمكنك الآن الدفع وتأكيد الحجز",
                            'type' => 'match_ready',
                            'notifiable_type' => OpenMatch::class,
                            'notifiable_id' => $match->id,
                            'is_read' => false,
                        ]);
                    }
                }

                return ['message' => 'تم الانضمام للماتش بنجاح'];
            }

            MatchPlayer::create([
                'match_id' => $match->id,
                'customer_id' => $customer->id,
                'status' => 'waitlisted',
                'joined_at' => null,
            ]);

            return ['message' => 'تم إضافتك لقائمة الانتظار'];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse($result['message'], null);
    }

    public function leave(int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $result = DB::transaction(function () use ($id, $customer) {
            $match = OpenMatch::with(['creator.user'])
                ->lockForUpdate()
                ->find($id);

            if (!$match) {
                return ['error' => ['message' => 'Match not found.', 'status' => 404]];
            }

            if (!in_array($match->status, ['waiting_players', 'ready_to_book'], true)) {
                return ['error' => ['message' => 'لا يمكن مغادرة ماتش منتهي', 'status' => 409]];
            }

            if ($match->creator_id === $customer->id) {
                return ['error' => ['message' => 'لا يمكنك مغادرة ماتش أنت صاحبه، يمكنك إلغاءه فقط', 'status' => 409]];
            }

            $player = MatchPlayer::where('match_id', $match->id)
                ->where('customer_id', $customer->id)
                ->whereIn('status', ['joined', 'waitlisted'])
                ->first();

            if (!$player) {
                return ['error' => ['message' => 'أنت لست في هذا الماتش', 'status' => 404]];
            }

            if ($player->status === 'waitlisted') {
                $player->update(['status' => 'left']);

                return ['message' => 'تم إزالتك من قائمة الانتظار'];
            }

            $player->update(['status' => 'left']);
            $match->update(['current_players' => max(0, $match->current_players - 1)]);

            if ($match->status === 'ready_to_book') {
                $match->update(['status' => 'waiting_players']);
            }

            $waitlisted = MatchPlayer::where('match_id', $match->id)
                ->where('status', 'waitlisted')
                ->orderBy('created_at')
                ->first();

            if ($waitlisted) {
                $waitlisted->update([
                    'status' => 'joined',
                    'joined_at' => now(),
                ]);

                $match->update(['current_players' => $match->current_players + 1]);

                if ($match->isFull()) {
                    $match->update(['status' => 'ready_to_book']);

                    if ($match->creator?->user) {
                        Notification::create([
                            'user_id' => $match->creator->user->id,
                            'title' => 'اكتمل العدد مجدداً!',
                            'message' => "اكتمل عدد لاعبي ماتش {$match->name} مجدداً، يمكنك الآن الدفع وتأكيد الحجز",
                            'type' => 'match_ready',
                            'notifiable_type' => OpenMatch::class,
                            'notifiable_id' => $match->id,
                            'is_read' => false,
                        ]);
                    }
                }

                if ($waitlisted->customer?->user) {
                    Notification::create([
                        'user_id' => $waitlisted->customer->user->id,
                        'title' => 'تم قبولك في الماتش!',
                        'message' => "تم قبولك في ماتش {$match->name}، استعد للعب!",
                        'type' => 'match_joined',
                        'notifiable_type' => OpenMatch::class,
                        'notifiable_id' => $match->id,
                        'is_read' => false,
                    ]);
                }
            }

            return ['message' => 'تم مغادرة الماتش بنجاح'];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse($result['message'], null);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $result = DB::transaction(function () use ($id, $customer) {
            $match = OpenMatch::with(['timeslot', 'players.customer.user'])
                ->lockForUpdate()
                ->find($id);

            if (!$match) {
                return ['error' => ['message' => 'Match not found.', 'status' => 404]];
            }

            if ($match->creator_id !== $customer->id) {
                return ['error' => ['message' => 'لا يمكنك إلغاء هذا الماتش', 'status' => 403]];
            }

            if (!in_array($match->status, ['waiting_players', 'ready_to_book'], true)) {
                return ['error' => ['message' => 'لا يمكن إلغاء ماتش بعد الدفع', 'status' => 409]];
            }

            $match->update(['status' => 'cancelled']);
            if ($match->timeslot) {
                $match->timeslot->update(['status' => 'available']);
            }

            foreach ($match->players as $player) {
                if ($player->customer_id === $customer->id) {
                    continue;
                }

                if ($player->customer?->user) {
                    Notification::create([
                        'user_id' => $player->customer->user->id,
                        'title' => 'تم إلغاء الماتش',
                        'message' => "قام صاحب الماتش بإلغاء ماتش {$match->name}",
                        'type' => 'match_cancelled',
                        'notifiable_type' => OpenMatch::class,
                        'notifiable_id' => $match->id,
                        'is_read' => false,
                    ]);
                }
            }

            return ['message' => 'تم إلغاء الماتش بنجاح'];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        return $this->successResponse($result['message'], null);
    }

    public function pay(PayMatchRequest $request, int $id): JsonResponse
    {
        $customer = $this->getCustomer();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $data = $request->validated();

        $result = DB::transaction(function () use ($id, $customer, $data, $request) {
            $match = OpenMatch::with(['court.maincourt.owner.user', 'timeslot'])
                ->lockForUpdate()
                ->find($id);

            if (!$match) {
                return ['error' => ['message' => 'Match not found.', 'status' => 404]];
            }

            if ($match->creator_id !== $customer->id) {
                return ['error' => ['message' => 'فقط صاحب الماتش يمكنه الدفع', 'status' => 403]];
            }

            if ($match->status !== 'ready_to_book') {
                return ['error' => ['message' => 'لا يمكن الدفع في الوقت الحالي', 'status' => 409]];
            }

            if ($match->timeslot?->status !== 'pending_match') {
                return ['error' => ['message' => 'الميعاد لم يعد متاحاً', 'status' => 409]];
            }

            $paymentMethod = PaymentMethod::find($data['payment_method_id']);
            if (!$paymentMethod || $paymentMethod->maincourt_id !== $match->court->maincourt_id) {
                return ['error' => ['message' => 'Payment method invalid.', 'status' => 422]];
            }

            $receiptFile = $request->file('receipt_image');
            $path = $receiptFile->store("receipts/{$customer->id}", 'public');
            $url = Storage::disk('public')->url($path);

            $booking = Booking::create([
                'customer_id' => $customer->id,
                'court_id' => $match->court_id,
                'timeslot_id' => $match->timeslot_id,
                'payment_method_id' => $paymentMethod->id,
                'total_price' => $match->court->price_per_hour,
                'receipt_image_url' => $url,
                'status' => 'pending',
            ]);

            $match->timeslot->update(['status' => 'booked']);
            $match->update([
                'booking_id' => $booking->id,
                'status' => 'booking_pending',
            ]);

            $ownerUserId = $match->court?->maincourt?->owner?->user?->id;
            if ($ownerUserId) {
                Notification::create([
                    'user_id' => $ownerUserId,
                    'title' => 'حجز ماتش جديد',
                    'message' => "لديك حجز جديد لماتش على {$match->court->name} بتاريخ {$match->timeslot->date} من {$match->timeslot->start_time} إلى {$match->timeslot->end_time}",
                    'type' => 'match_booking',
                    'notifiable_type' => Booking::class,
                    'notifiable_id' => $booking->id,
                    'is_read' => false,
                ]);
            }

            $match->load(['court.maincourt.primaryImage', 'timeslot', 'creator.user']);

            return ['booking' => $booking, 'match' => $match];
        });

        if (isset($result['error'])) {
            return $this->errorResponse($result['error']['message'], $result['error']['status']);
        }

        $booking = $result['booking'];
        $booking->load(['court.maincourt', 'timeslot', 'paymentMethod']);

        return $this->successResponse('Match booking created.', [
            'booking' => new BookingResource($booking),
            'match' => new OpenMatchResource($result['match']),
        ], 201);
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
