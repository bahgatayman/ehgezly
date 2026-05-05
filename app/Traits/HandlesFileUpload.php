<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesFileUpload
{
    protected function uploadFile(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 'public');
        return $this->publicStorageUrl($path);
    }

    protected function deleteFile(string $url): void
    {
        $path = $this->extractStoragePath($url);
        if (!$path) {
            return;
        }
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }

    private function extractStoragePath(string $url): ?string
    {
        if ($url === '') {
            return null;
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            $parsedPath = parse_url($url, PHP_URL_PATH);
            if (is_string($parsedPath) && Str::startsWith($parsedPath, '/storage/')) {
                return ltrim(Str::after($parsedPath, '/storage/'), '/');
            }

            return null;
        }

        if (Str::startsWith($url, '/storage/')) {
            return ltrim(Str::after($url, '/storage/'), '/');
        }

        if (Str::startsWith($url, 'storage/')) {
            return ltrim(Str::after($url, 'storage/'), '/');
        }

        return null;
    }

    private function publicStorageUrl(string $path): string
    {
        $request = request();
        if ($request) {
            $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
            return $baseUrl . '/storage/' . ltrim($path, '/');
        }

        return Storage::disk('public')->url($path);
    }
}
