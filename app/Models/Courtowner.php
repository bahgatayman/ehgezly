<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courtowner extends Model
{
    protected $fillable = [
        'user_id',
        'ownership_proof_url',
        'total_revenue',
        'app_due_amount',
        'app_paid_amount',
        'remaining_balance',
    ];

    protected $casts = [
        'total_revenue'     => 'decimal:2',
        'app_due_amount'    => 'decimal:2',
        'app_paid_amount'   => 'decimal:2',
        'remaining_balance' => 'decimal:2',
    ];

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function maincourts()
    {
        return $this->hasMany(Maincourt::class, 'owner_id');
    }
}