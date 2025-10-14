<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Building extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'code', 'description', 'grid_width', 'grid_height', 'x_position','y_position'
    ];

    public function accessPoints(){
        return $this-> hasMany(AccessPoint::class); 
    }

    public function rooms(){
        return $this->hasMany(Room::class);
    }
    
}
