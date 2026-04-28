<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'can_book',
    ];

    protected $casts = [
        'can_book' => 'boolean',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class);
    }

    public function createdMatches()
    {
        return $this->hasMany(OpenMatch::class, 'creator_id');
    }

    public function matchPlayers()
    {
        return $this->hasMany(MatchPlayer::class);
    }
}