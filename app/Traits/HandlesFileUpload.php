<?php

namespace App\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait HandlesFileUpload
{
    protected function uploadFile(UploadedFile $file, string $folder): string
    {
        $path = $file->store($folder, 'public');
        return asset('storage/' . $path);
    }

    protected function deleteFile(string $url): void
    {
        $path = str_replace(asset('storage/') . '/', '', $url);
        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
