<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PaymentMethod extends Model
{
    protected $fillable = [
        'maincourt_id',
        'type',
        'identifier',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    // Relationships
    public function maincourt()
    {
        return $this->belongsTo(Maincourt::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }
}