<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'customer_id',
        'court_id',
        'timeslot_id',
        'payment_method_id',
        'total_price',
        'receipt_image_url',
        'status',
        'rejection_reason',
    ];

    protected $casts = [
        'total_price' => 'decimal:2',
    ];

    // Relationships
    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function court()
    {
        return $this->belongsTo(Court::class);
    }

    public function timeslot()
    {
        return $this->belongsTo(Timeslot::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    // Helpers
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);
        $this->timeslot->update(['status' => 'booked']);
    }

    public function reject(string $reason): void
    {
        $this->update([
            'status'           => 'rejected',
            'rejection_reason' => $reason,
        ]);
        $this->timeslot->update(['status' => 'available']);
    }
}