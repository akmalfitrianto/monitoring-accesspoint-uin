<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Room extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id', 'name', 'code', 'x_position', 'y_position', 'width' , 'height',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function accessPoints()
    {
        return $this->hasMany(AccessPoint::class, 'room_id');
    }
}
