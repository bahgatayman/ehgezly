<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Amenity extends Model
{
    protected $fillable = [
        'name',
        'icon',
    ];

    // Relationships
    public function maincourts()
    {
        return $this->belongsToMany(Maincourt::class, 'maincourt_amenities');
    }
}