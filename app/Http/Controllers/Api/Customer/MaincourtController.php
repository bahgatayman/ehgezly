<?php

namespace App\Http\Controllers\Api\Customer;

use App\Http\Controllers\Controller;
use App\Http\Resources\Customer\MaincourtResource;
use App\Http\Resources\Customer\RatingResource;
use App\Models\Customer;
use App\Models\Maincourt;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Validator;

class MaincourtController extends Controller
{
    public function index(): JsonResponse
    {
        $request = request();

        $validator = Validator::make($request->query(), [
            'latitude' => 'nullable|numeric|between:-90,90',
            'longitude' => 'nullable|numeric|between:-180,180',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'data' => $validator->errors(),
            ], 422);
        }

        $hasLat = $request->filled('latitude');
        $hasLng = $request->filled('longitude');

        if ($hasLat !== $hasLng) {
            return response()->json([
                'success' => false,
                'message' => 'يجب إرسال خط العرض وخط الطول معاً',
                'data' => null,
            ], 422);
        }

        $query = Maincourt::query()
            ->where('status', 'active')
            ->where('is_verified', true)
            ->with(['primaryImage', 'amenities', 'workingHours'])
            ->withCount('courts')
            ->withCount('ratings')
            ->withAvg('ratings', 'rating');

        $search = $request->query('search');
        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $amenities = Arr::wrap($request->query('amenities'));
        $amenities = array_values(array_filter($amenities, fn ($value) => $value !== null && $value !== ''));
        if (count($amenities) > 0) {
            $query->whereHas('amenities', function ($q) use ($amenities) {
                $q->whereIn('amenities.id', $amenities);
            }, '=', count($amenities));
        }

        $day = $request->query('day');
        if ($day) {
            $query->whereHas('workingHours', function ($q) use ($day) {
                $q->where('day_of_week', $day)->where('is_open', true);
            });
        }

        if ($hasLat && $hasLng) {
            $latitude = (float) $request->query('latitude');
            $longitude = (float) $request->query('longitude');

            $query->selectRaw(
                "maincourts.*, (6371 * acos(cos(radians(?)) * cos(radians(latitude)) * cos(radians(longitude) - radians(?)) + sin(radians(?)) * sin(radians(latitude)))) AS distance",
                [$latitude, $longitude, $latitude]
            )->orderBy('distance', 'asc');
        } else {
            $query->orderByDesc('created_at');
        }

        $maincourts = $query->get();

        $message = $hasLat && $hasLng ? 'الملاعب القريبة منك' : 'جميع الملاعب';

        return $this->successResponse($message, MaincourtResource::collection($maincourts));
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

        $customer = Customer::where('user_id', auth()->id())->first();
        if (!$customer) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $myRating = $customer->ratings()
            ->where('maincourt_id', $maincourt->id)
            ->first();

        $recentRatings = $maincourt->ratings()
            ->with('customer.user')
            ->orderByDesc('created_at')
            ->limit(5)
            ->get();

        $maincourt->setAttribute('average_rating', $maincourt->averageRating());
        $maincourt->setAttribute('ratings_count', $maincourt->ratingsCount());
        $maincourt->setAttribute('my_rating', $myRating);
        $maincourt->setAttribute('recent_ratings', $recentRatings);

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
