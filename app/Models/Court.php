<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Court extends Model
{
    protected $fillable = [
        'maincourt_id',
        'name',
        'description',
        'type',
        'surface_type',
        'price_per_hour',
        'status',
        'is_open',
    ];

    protected $casts = [
        'price_per_hour' => 'decimal:2',
        'is_open'        => 'boolean',
    ];

    // Relationships
    public function maincourt()
    {
        return $this->belongsTo(Maincourt::class);
    }

    public function timeslots()
    {
        return $this->hasMany(Timeslot::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
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
    public function availableTimeslots(string $date)
    {
        return $this->timeslots()
                    ->where('date', $date)
                    ->where('status', 'available')
                    ->get();
    }
}