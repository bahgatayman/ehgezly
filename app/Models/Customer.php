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

    public function ratings()
    {
        return $this->hasMany(MaincourtRating::class);
    }

    public function bookingCount(): int
    {
        return $this->bookings()->count();
    }

    public function activeBookingsCount(): int
    {
        return $this->bookings()
            ->whereIn('status', ['pending', 'confirmed'])
            ->count();
    }

    public function completedBookingsCount(): int
    {
        return $this->bookings()
            ->where('status', 'completed')
            ->count();
    }

    public function cancelledBookingsCount(): int
    {
        return $this->bookings()
            ->where('status', 'cancelled')
            ->count();
    }

    public function rejectedBookingsCount(): int
    {
        return $this->bookings()
            ->where('status', 'rejected')
            ->count();
    }

    public function hasRatedMaincourt(int $maincourt_id): bool
    {
        return $this->ratings()
            ->where('maincourt_id', $maincourt_id)
            ->exists();
    }

    public function hasCompletedBookingAt(int $maincourt_id): bool
    {
        return $this->bookings()
            ->whereHas('court', function ($q) use ($maincourt_id) {
                $q->where('maincourt_id', $maincourt_id);
            })
            ->where('status', 'completed')
            ->exists();
    }
}