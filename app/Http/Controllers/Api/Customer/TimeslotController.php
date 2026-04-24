<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\TimeslotResource;
use App\Models\Court;
use App\Models\Timeslot;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class TimeslotController extends Controller
{
    public function index(int $courtId): JsonResponse
    {
        $date = request()->query('date');
        if (!$date || !$this->isValidDate($date)) {
            return $this->errorResponse('Invalid date.', 422);
        }

        $dateCarbon = Carbon::createFromFormat('Y-m-d', $date)->startOfDay();
        $today = Carbon::today();
        $maxDate = Carbon::today()->addDays(30);

        if ($dateCarbon->lt($today) || $dateCarbon->gt($maxDate)) {
            return $this->errorResponse('Invalid date.', 422);
        }

        $court = Court::with('maincourt.workingHours')->find($courtId);
        if (
            !$court
            || $court->status !== 'open'
            || !$court->is_open
            || !$court->maincourt
            || $court->maincourt->status !== 'active'
            || !$court->maincourt->is_verified
        ) {
            return $this->errorResponse('Court not found.', 404);
        }

        $day = strtolower($dateCarbon->format('l'));
        $workingHour = $court->maincourt->workingHours->firstWhere('day_of_week', $day);

        if (!$workingHour || !$workingHour->is_open) {
            return $this->successResponse('المكان مغلق هذا اليوم', []);
        }

        $timeslots = DB::transaction(function () use ($court, $date, $workingHour) {
            $existing = $court->timeslots()->where('date', $date)->lockForUpdate()->get();
            if ($existing->isNotEmpty()) {
                return $existing;
            }

            $this->generateTimeslots($court->id, $date, $workingHour->open_time, $workingHour->close_time);

            return $court->timeslots()->where('date', $date)->get();
        });

        $available = $timeslots->where('status', 'available')->values();

        return $this->successResponse('Timeslots retrieved.', TimeslotResource::collection($available));
    }

    private function generateTimeslots(int $courtId, string $date, string $openTime, string $closeTime): void
    {
        $start = $this->parseTime($openTime);
        $end = $this->parseTime($closeTime);

        if (!$start || !$end || $start->gte($end)) {
            return;
        }

        while ($start->lt($end)) {
            $slotEnd = $start->copy()->addHour();
            if ($slotEnd->gt($end)) {
                break;
            }

            Timeslot::create([
                'court_id' => $courtId,
                'date' => $date,
                'start_time' => $start->format('H:i:s'),
                'end_time' => $slotEnd->format('H:i:s'),
                'status' => 'available',
            ]);

            $start = $slotEnd;
        }
    }

    private function parseTime(string $value): ?Carbon
    {
        try {
            return Carbon::createFromFormat('H:i:s', $value);
        } catch (\Throwable $e) {
            try {
                return Carbon::createFromFormat('H:i', $value);
            } catch (\Throwable $e2) {
                return null;
            }
        }
    }

    private function isValidDate(string $value): bool
    {
        try {
            Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            return true;
        } catch (\Throwable $e) {
            return false;
        }
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
