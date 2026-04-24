<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\SyncAmenitiesRequest;
use App\Http\Resources\AmenityResource;
use App\Models\Amenity;
use App\Models\Courtowner;
use App\Models\Maincourt;
use Illuminate\Http\JsonResponse;

class AmenityController extends Controller
{
    public function index(): JsonResponse
    {
        $amenities = Amenity::all();

        return $this->successResponse('Amenities retrieved.', AmenityResource::collection($amenities));
    }

    public function sync(SyncAmenitiesRequest $request, int $id): JsonResponse
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

        $amenityIds = $request->validated()['amenity_ids'];
        $maincourt->amenities()->sync($amenityIds);
        $maincourt->load('amenities');

        return $this->successResponse(
            'Amenities synced.',
            AmenityResource::collection($maincourt->amenities),
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
