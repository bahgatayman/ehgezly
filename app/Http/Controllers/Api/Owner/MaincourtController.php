<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreMaincourtRequest;
use App\Http\Requests\Owner\UpdateMaincourtRequest;
use App\Http\Resources\MaincourtResource;
use App\Models\Booking;
use App\Models\Courtowner;
use App\Models\Maincourt;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class MaincourtController extends Controller
{
    public function index(): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourts = Maincourt::where('owner_id', $owner->id)
            ->with(['courts', 'amenities', 'paymentMethods', 'workingHours', 'primaryImage'])
            ->get();

        return $this->successResponse('Maincourts retrieved.', MaincourtResource::collection($maincourts));
    }

    public function store(StoreMaincourtRequest $request): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $data = $request->validated();
        $data['owner_id'] = $owner->id;
        $data['status'] = 'inactive';
        $data['is_verified'] = false;

        $maincourt = Maincourt::create($data);
        $maincourt->load(['courts', 'amenities', 'paymentMethods', 'workingHours', 'primaryImage']);

        return $this->successResponse('Maincourt created.', new MaincourtResource($maincourt), 201);
    }

    public function show(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::with([
            'courts.primaryImage',
            'amenities',
            'paymentMethods',
            'workingHours',
            'images',
        ])->find($id);

        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        return $this->successResponse('Maincourt retrieved.', new MaincourtResource($maincourt));
    }

    public function update(UpdateMaincourtRequest $request, int $id): JsonResponse
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

        $data = $request->validated();
        unset($data['status'], $data['is_verified']);
        $maincourt->update($data);
        $maincourt->load(['courts', 'amenities', 'paymentMethods', 'workingHours', 'primaryImage']);

        return $this->successResponse('Maincourt updated.', new MaincourtResource($maincourt));
    }

    public function destroy(int $id): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $maincourt = Maincourt::with(['courts', 'images', 'courts.images'])->find($id);
        if (!$maincourt) {
            return $this->errorResponse('Maincourt not found.', 404);
        }

        if ($maincourt->owner_id !== $owner->id) {
            return $this->errorResponse('Forbidden.', 403);
        }

        if ($this->hasConfirmedFutureBookings($maincourt->id)) {
            return $this->errorResponse('Maincourt has confirmed future bookings.', 409);
        }

        DB::transaction(function () use ($maincourt) {
            $this->deleteImageCollection($maincourt->images);

            foreach ($maincourt->courts as $court) {
                $this->deleteImageCollection($court->images);
            }

            $maincourt->amenities()->detach();
            $maincourt->paymentMethods()->delete();
            $maincourt->workingHours()->delete();

            foreach ($maincourt->courts as $court) {
                $court->delete();
            }

            $maincourt->delete();
        });

        return $this->successResponse('Maincourt deleted.', null);
    }

    private function getOwner(): ?Courtowner
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Courtowner::where('user_id', $user->id)->first();
    }

    private function hasConfirmedFutureBookings(int $maincourtId): bool
    {
        $today = Carbon::today()->toDateString();

        return Booking::where('status', 'confirmed')
            ->whereHas('court', function ($query) use ($maincourtId) {
                $query->where('maincourt_id', $maincourtId);
            })
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
