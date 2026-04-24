<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreCourtRequest;
use App\Http\Requests\Owner\UpdateCourtRequest;
use App\Http\Resources\CourtResource;
use App\Models\Booking;
use App\Models\Court;
use App\Models\Courtowner;
use App\Models\Maincourt;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class CourtController extends Controller
{
    public function index(int $maincourtId): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($maincourtId);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $today = Carbon::today()->toDateString();

        $courts = $maincourt->courts()
            ->with([
                'primaryImage',
                'timeslots' => function ($query) use ($today) {
                    $query->where('date', '>=', $today)
                        ->where('status', 'available');
                },
            ])
            ->get();

        return $this->successResponse('Courts retrieved.', CourtResource::collection($courts));
    }

    public function store(StoreCourtRequest $request, int $maincourtId): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($maincourtId);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $data = $request->validated();
        $data['maincourt_id'] = $maincourt->id;

        $court = Court::create($data);
        $court->load(['primaryImage']);

        return $this->successResponse('Court created.', new CourtResource($court), 201);
    }

    public function show(int $maincourtId, int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($maincourtId);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $today = Carbon::today()->toDateString();

        $court = Court::with([
            'images',
            'timeslots' => function ($query) use ($today) {
                $query->where('date', '>=', $today);
            },
        ])->where('maincourt_id', $maincourt->id)
            ->where('id', $id)
            ->first();

        if (!$court) {
            return $this->errorResponse('Court not found.', 404);
        }

        return $this->successResponse('Court retrieved.', new CourtResource($court));
    }

    public function update(UpdateCourtRequest $request, int $maincourtId, int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($maincourtId);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $court = Court::where('maincourt_id', $maincourt->id)
            ->where('id', $id)
            ->first();

        if (!$court) {
            return $this->errorResponse('Court not found.', 404);
        }

        $court->update($request->validated());
        $court->load(['primaryImage']);

        return $this->successResponse('Court updated.', new CourtResource($court));
    }

    public function destroy(int $maincourtId, int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::find($maincourtId);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $court = Court::with('images')
            ->where('maincourt_id', $maincourt->id)
            ->where('id', $id)
            ->first();

        if (!$court) {
            return $this->errorResponse('Court not found.', 404);
        }

        if ($this->hasConfirmedFutureBookings($court->id)) {
            return $this->errorResponse('Court has confirmed future bookings.', 409);
        }

        $this->deleteImageCollection($court->images);
        $court->delete();

        return $this->successResponse('Court deleted.', null);
    }

    private function getOwner(): ?Courtowner
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Courtowner::where('user_id', $user->id)->first();
    }

    private function hasConfirmedFutureBookings(int $courtId): bool
    {
        $today = Carbon::today()->toDateString();

        return Booking::where('status', 'confirmed')
            ->where('court_id', $courtId)
            ->whereHas('timeslot', function ($query) use ($today) {
                $query->whereDate('date', '>=', $today);
            })
            ->exists();
    }

    private function deleteImageCollection($images): void
    {
        foreach ($images as $image) {
            $this->deleteImageFile($image->url);
            $image->delete();
        }
    }

    private function deleteImageFile(string $url): void
    {
        $path = $this->publicPathFromUrl($url);
        if ($path) {
            Storage::disk('public')->delete($path);
        }
    }

    private function publicPathFromUrl(string $url): ?string
    {
        if (str_starts_with($url, '/storage/')) {
            return substr($url, 9);
        }

        if (str_starts_with($url, 'storage/')) {
            return substr($url, 8);
        }

        $parsedPath = parse_url($url, PHP_URL_PATH);
        if ($parsedPath && str_starts_with($parsedPath, '/storage/')) {
            return substr($parsedPath, 9);
        }

        return null;
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
