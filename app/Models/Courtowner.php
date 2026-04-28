<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Courtowner extends Model
{
    protected $fillable = [
        'user_id',
        'ownership_proof_url',
        'commission_percentage',
        'total_revenue',
        'app_due_amount',
        'remaining_balance',
    ];

    protected $casts = [
        'commission_percentage' => 'decimal:2',
        'total_revenue'     => 'decimal:2',
        'app_due_amount'    => 'decimal:2',
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

    public function payments()
    {
        return $this->hasMany(OwnerPayment::class, 'owner_id');
    }
}