<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AccessPoint extends Model
{
    use HasFactory;

    protected $fillable = [
        'building_id','room_id', 'name', 'mac_address','x_position','y_position','signal_strength','status'
    ];

    public function building() {
        return $this->belongsTo(Building::class);
    }

    public function room(){
        return $this->belongsTo(Room::class);
    }

    public function isActive() {
        return $this->status === 'active';
    }
}
