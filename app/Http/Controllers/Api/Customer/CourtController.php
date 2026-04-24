<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\CourtResource;
use App\Models\Court;
use App\Models\Maincourt;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

class CourtController extends Controller
{
    public function index(int $maincourtId): JsonResponse
    {
        $maincourt = Maincourt::where('status', 'active')
            ->where('is_verified', true)
            ->find($maincourtId);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        $query = Court::where('maincourt_id', $maincourt->id)
            ->where('status', 'open')
            ->where('is_open', true)
            ->with(['primaryImage']);

        $type = request()->query('type');
        if ($type) {
            $query->where('type', $type);
        }

        $surfaceType = request()->query('surface_type');
        if ($surfaceType) {
            $query->where('surface_type', $surfaceType);
        }

        $date = request()->query('date');
        if ($date) {
            if (!$this->isValidDate($date)) {
                return $this->errorResponse('Invalid date.', 422);
            }

            $query->withCount([
                'timeslots as available_timeslots_count' => function ($q) use ($date) {
                    $q->where('date', $date)->where('status', 'available');
                },
            ]);
        }

        $courts = $query->get();

        return $this->successResponse('Courts retrieved.', CourtResource::collection($courts));
    }

    public function show(int $maincourtId, int $id): JsonResponse
    {
        $maincourt = Maincourt::where('status', 'active')
            ->where('is_verified', true)
            ->find($maincourtId);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        $court = Court::where('maincourt_id', $maincourt->id)
            ->where('id', $id)
            ->with([
                'images',
                'maincourt.workingHours',
                'maincourt.paymentMethods',
            ])
            ->first();

        if (!$court) {
            return $this->errorResponse('Court not found.', 404);
        }

        return $this->successResponse('Court retrieved.', new CourtResource($court));
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
