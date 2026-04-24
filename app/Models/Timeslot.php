<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Timeslot extends Model
{
    protected $fillable = [
        'court_id',
        'date',
        'start_time',
        'end_time',
        'status',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    // Relationships
    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function booking()
    {
        return $this->hasOne(Booking::class);
    }

    // Helpers
    public function isAvailable(): bool
    {
        return $this->status === 'available';
    }
}