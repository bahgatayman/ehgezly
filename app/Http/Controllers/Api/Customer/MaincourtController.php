<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\MaincourtResource;
use App\Models\Maincourt;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;

class MaincourtController extends Controller
{
    public function index(): JsonResponse
    {
        $query = Maincourt::query()
            ->where('status', 'active')
            ->where('is_verified', true)
            ->with(['primaryImage', 'amenities', 'workingHours'])
            ->withCount('courts');

        $search = request()->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $amenities = Arr::wrap(request()->query('amenities'));
        $amenities = array_values(array_filter($amenities, fn ($value) => $value !== null && $value !== ''));
        if (count($amenities) > 0) {
            $query->whereHas('amenities', function ($q) use ($amenities) {
                $q->whereIn('amenities.id', $amenities);
            }, '=', count($amenities));
        }

        $day = request()->query('day');
        if ($day) {
            $query->whereHas('workingHours', function ($q) use ($day) {
                $q->where('day_of_week', $day)->where('is_open', true);
            });
        }

        $maincourts = $query->get();

        return $this->successResponse('Maincourts retrieved.', MaincourtResource::collection($maincourts));
    }

    public function show(int $id): JsonResponse
    {
        $maincourt = Maincourt::where('status', 'active')
            ->where('is_verified', true)
            ->with([
                'images',
                'amenities',
                'workingHours',
                'paymentMethods' => function ($q) {
                    $q->where('is_active', true);
                },
                'courts' => function ($q) {
                    $q->where('status', 'open')->where('is_open', true);
                },
                'courts.primaryImage',
            ])
            ->find($id);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        return $this->successResponse('Maincourt retrieved.', new MaincourtResource($maincourt));
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
