<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class Image extends Model
{
    protected $fillable = [
        'imageable_id',
        'imageable_type',
        'url',
        'is_primary',
    ];

    protected $casts = [
        'is_primary' => 'boolean',
    ];

    public function getUrlAttribute($value): ?string
    {
        if (!$value || !is_string($value)) {
            return $value;
        }

        $storagePath = $this->extractStoragePath($value);
        if ($storagePath) {
            $request = request();
            if ($request) {
                $baseUrl = rtrim($request->getSchemeAndHttpHost(), '/');
                return $baseUrl . '/storage/' . ltrim($storagePath, '/');
            }

            return Storage::disk('public')->url($storagePath);
        }

        return $value;
    }

    // Relationships
    public function imageable()
    {
        return $this->morphTo();
    }

    private function extractStoragePath(string $url): ?string
    {
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
}