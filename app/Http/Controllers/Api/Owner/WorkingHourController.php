<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreWorkingHoursRequest;
use App\Http\Resources\WorkingHourResource;
use App\Models\Courtowner;
use App\Models\Maincourt;
use Illuminate\Http\JsonResponse;

class WorkingHourController extends Controller
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

        $hours = $maincourt->workingHours()->get();

        return $this->successResponse('Working hours retrieved.', WorkingHourResource::collection($hours));
    }

    public function store(StoreWorkingHoursRequest $request, int $id): JsonResponse
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

        $hours = $request->validated()['hours'];

        foreach ($hours as $hour) {
            $existing = $maincourt->workingHours()
                ->where('day_of_week', $hour['day_of_week'])
                ->first();

            $openTime = $hour['open_time'] ?? ($existing?->open_time ?? '00:00');
            $closeTime = $hour['close_time'] ?? ($existing?->close_time ?? '00:00');

            $maincourt->workingHours()->updateOrCreate(
                ['day_of_week' => $hour['day_of_week']],
                [
                    'open_time' => $openTime,
                    'close_time' => $closeTime,
                    'is_open' => $hour['is_open'],
                ]
            );
        }

        $maincourt->load('workingHours');

        return $this->successResponse(
            'Working hours saved.',
            WorkingHourResource::collection($maincourt->workingHours),
            201
        );
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
