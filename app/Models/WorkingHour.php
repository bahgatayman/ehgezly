<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WorkingHour extends Model
{
    protected $fillable = [
        'maincourt_id',
        'day_of_week',
        'open_time',
        'close_time',
        'is_open',
    ];

    protected $casts = [
        'is_open' => 'boolean',
    ];

    // Relationships
    public function maincourt()
    {
        return $this->belongsTo(Maincourt::class);
    }
}