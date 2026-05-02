<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaincourtRating extends Model
{
    protected $fillable = [
        'maincourt_id',
        'customer_id',
        'rating',
        'comment',
    ];

    protected $casts = [
        'rating' => 'integer',
    ];

    public function maincourt()
    {
        return $this->belongsTo(Maincourt::class);
    }

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }
}
