<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Maincourt extends Model
{
    protected $fillable = [
        'owner_id',
        'name',
        'description',
        'address',
        'map_link',
        'latitude',
        'longitude',
        'status',
        'is_verified',
    ];

    protected $casts = [
        'latitude'    => 'decimal:8',
        'longitude'   => 'decimal:8',
        'is_verified' => 'boolean',
    ];

    // Relationships
    public function owner()
    {
        return $this->belongsTo(Courtowner::class, 'owner_id');
    }

    public function courts()
    {
        return $this->hasMany(Court::class);
    }

    public function amenities()
    {
        return $this->belongsToMany(Amenity::class, 'maincourt_amenities');
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function workingHours()
    {
        return $this->hasMany(WorkingHour::class);
    }

    public function images()
    {
        return $this->morphMany(Image::class, 'imageable');
    }

    public function primaryImage()
    {
        return $this->morphOne(Image::class, 'imageable')
                    ->where('is_primary', true);
    }

    // Helpers
    public function isActive(): bool
    {
        return $this->status === 'active' && $this->is_verified;
    }
}