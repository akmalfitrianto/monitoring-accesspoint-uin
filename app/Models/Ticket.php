<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'status',
        'building_id',
        'room_id',
        'access_point_id',
        'reported_by',
        'assigned_to',
        'resolved_at',
    ];

    protected $cast = [
        'resolved_at' => 'datetime',
    ];

    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function accessPoint()
    {
        return $this->belongsTo(AccessPoint::class);
    }

    public function reporter()
    {
        return $this->belongsTo(User::class, 'reported_by');
    }

    public function technician()
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }
}
