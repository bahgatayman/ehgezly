<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MatchPlayer extends Model
{
    protected $fillable = [
        'match_id',
        'customer_id',
        'status',
        'joined_at',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
    ];

    public function match()
    {
        return $this->belongsTo(OpenMatch::class, 'match_id');
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
