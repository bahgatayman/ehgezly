<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OwnerPayment extends Model
{
    protected $fillable = [
        'owner_id',
        'amount',
        'payment_type',
        'receipt_image_url',
        'notes',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function owner()
    {
        return $this->belongsTo(Courtowner::class, 'owner_id');
    }
}
