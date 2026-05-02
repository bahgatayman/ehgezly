<?php

namespace App\Http\Controllers\Api\Owner;

use App\Http\Controllers\Controller;
use App\Http\Requests\Owner\StoreImagesRequest;
use App\Http\Resources\ImageResource;
use App\Models\Court;
use App\Models\Courtowner;
use App\Models\Image;
use App\Models\Maincourt;
use App\Traits\HandlesFileUpload;
use Illuminate\Http\JsonResponse;

class ImageController extends Controller
{
    use HandlesFileUpload;
    public function storeMaincourtImages(StoreImagesRequest $request, int $id): JsonResponse
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

        $this->storeImages($maincourt, "maincourts/{$maincourt->id}", $request);
        $images = $maincourt->images()->get();

        return $this->successResponse('Images uploaded.', ImageResource::collection($images), 201);
    }

    public function storeCourtImages(StoreImagesRequest $request, int $maincourtId, int $id): JsonResponse
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

        $court = Court::where('maincourt_id', $maincourt->id)->where('id', $id)->first();
        if (!$court) {
            return $this->errorResponse('Court not found.', 404);
        }

        $this->storeImages($court, "courts/{$court->id}", $request);
        $images = $court->images()->get();

        return $this->successResponse('Images uploaded.', ImageResource::collection($images), 201);
    }

    public function destroy(int $imageId): JsonResponse
    {
        $owner = $this->getOwner();
        if (!$owner) {
            return $this->errorResponse('Forbidden.', 403);
        }

        $image = Image::find($imageId);
        if (!$image) {
            return $this->errorResponse('Image not found.', 404);
        }

        $imageable = $image->imageable;
        if (!$imageable) {
            return $this->errorResponse('Image not found.', 404);
        }

        if ($imageable instanceof Maincourt) {
            if ($imageable->owner_id !== $owner->id) {
                return $this->errorResponse('Forbidden.', 403);
            }
        } elseif ($imageable instanceof Court) {
            $imageable->load('maincourt');
            if (!$imageable->maincourt || $imageable->maincourt->owner_id !== $owner->id) {
                return $this->errorResponse('Forbidden.', 403);
            }
        } else {
            return $this->errorResponse('Forbidden.', 403);
        }

        $wasPrimary = $image->is_primary;
        $this->deleteImageFile($image->url);
        $image->delete();

        if ($wasPrimary) {
            $newPrimary = $imageable->images()->orderBy('id')->first();
            if ($newPrimary) {
                $newPrimary->update(['is_primary' => true]);
            }
        }

        return $this->successResponse('Image deleted.', null);
    }

    private function storeImages($imageable, string $directory, StoreImagesRequest $request): void
    {
        $files = $request->file('images', []);
        if (count($files) === 0) {
            return;
        }

        $primaryIndex = $request->input('primary_index', 0);
        if ($primaryIndex === null || $primaryIndex < 0 || $primaryIndex >= count($files)) {
            $primaryIndex = 0;
        }

        $imageable->images()->update(['is_primary' => false]);

        foreach ($files as $index => $file) {
            $url = $this->uploadFile($file, $directory);
            $imageable->images()->create([
                'url' => $url,
                'is_primary' => $index === $primaryIndex,
            ]);
        }
    }

    private function getOwner(): ?Courtowner
    {
        $user = auth()->user();
        if (!$user) {
            return null;
        }

        return Courtowner::where('user_id', $user->id)->first();
    }

    private function deleteImageFile(string $url): void
    {
        $this->deleteFile($url);
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
