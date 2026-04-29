<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OpenMatch extends Model
{
    protected $fillable = [
        'court_id',
        'timeslot_id',
        'creator_id',
        'booking_id',
        'name',
        'description',
        'required_players',
        'current_players',
        'status',
    ];

    protected $casts = [
        'required_players' => 'integer',
        'current_players' => 'integer',
    ];

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function timeslot()
    {
        return $this->belongsTo(Timeslot::class);
    }

    public function creator()
    {
        return $this->belongsTo(Customer::class, 'creator_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function players()
    {
        return $this->hasMany(MatchPlayer::class, 'match_id');
    }

    public function joinedPlayers()
    {
        return $this->hasMany(MatchPlayer::class, 'match_id')->where('status', 'joined');
    }

    public function waitlistedPlayers()
    {
        return $this->hasMany(MatchPlayer::class, 'match_id')->where('status', 'waitlisted');
    }

    public function isFull(): bool
    {
        return $this->current_players >= $this->required_players;
    }

    public function spotsLeft(): int
    {
        return max(0, $this->required_players - $this->current_players);
    }

    public function isActive(): bool
    {
        return in_array($this->status, ['waiting_players', 'ready_to_book'], true);
    }
}
