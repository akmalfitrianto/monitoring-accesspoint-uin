<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
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


    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    // ==================== RELATIONSHIPS ====================
    
    public function building()
    {
        return $this->belongsTo(Building::class);
    }

    public function room()
    {
        return $this->belongsTo(Room::class);
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

    public function logs()
    {
        return $this->hasMany(TicketLog::class);
    }

    // ==================== STATUS HELPERS ====================
    
    public function isOpen()
    {
        return $this->status === 'open';
    }

    public function isInProgress()
    {
        return $this->status === 'in_progress';
    }

    public function isResolved()
    {
        return $this->status === 'resolved';
    }

    public function isClosed()
    {
        return $this->status === 'closed';
    }

    public function isUnassigned()
    {
        return is_null($this->assigned_to);
    }

    // ==================== SCOPES ====================
    
    public function scopeOpen(Builder $query)
    {
        return $query->where('status', 'open');
    }

    public function scopeInProgress(Builder $query)
    {
        return $query->where('status', 'in_progress');
    }

    public function scopeResolved(Builder $query)
    {
        return $query->where('status', 'resolved');
    }

    public function scopeClosed(Builder $query)
    {
        return $query->where('status', 'closed');
    }

    public function scopeUnassigned(Builder $query)
    {
        return $query->whereNull('assigned_to');
    }

    public function scopeAssignedTo(Builder $query, $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeReportedBy(Builder $query, $userId)
    {
        return $query->where('reported_by', $userId);
    }

    public function scopeForAccessPoint(Builder $query, $accessPointId)
    {
        return $query->where('access_point_id', $accessPointId);
    }

    // Scope untuk ticket yang belum selesai (open atau in_progress)
    public function scopeActive(Builder $query)
    {
        return $query->whereIn('status', ['open', 'in_progress']);
    }

    // booted
    protected static function booted(): void
    {
    static::created(function ($ticket) {
        $ticket->logs()->create([
            'user_id' => Auth::id(),
            'action' => 'ticket_created',
            'new_status' => $ticket->status,
            'notes' => 'Tiket dibuat',
            ]);
        
        Notification::make()
            ->title('Tiket Baru dibuat')
            ->body("Tiket '{$ticket->title}' telah dibuat " .auth()->user()->name)
            ->success()
            ->sendToDatabase(auth()->user());

        $recipients = \App\Models\User::role(['admin', 'teknisi'])->get();

        foreach ($recipients as $user) {
        Notification::make()
            ->title('Tiket Baru Dibuat')
            ->body("{$ticket->reporter->name} membuat tiket baru: '{$ticket->title}'.")
            ->info()
            ->sendToDatabase($user);
        }
        });

    static::updating(function ($ticket) {
        if ($ticket->isDirty('status')) {
            $old = $ticket->getOriginal('status');
            $new = $ticket->status;

            $ticket->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'status_changed',
                'old_status' => $old,
                'new_status' => $new,
                'notes' => "Status berubah dari {$old} menjadi {$new}",
            ]);

            if ($new === 'resolved' && $ticket->accessPoint) {
                $ticket->accessPoint->update(['status'=>'active']);
            }

            if ($new === 'open' && $ticket->accessPoint) {
                $ticket->accessPoint->update(['status'=>'maintenance']);
            }

            if ($ticket->reporter) {
                Notification::make()
                    ->title('Status Tiket Berubah')
                    ->body("Status tiket '{$ticket->title}' berubah dari {$old} menjadi {$new}")
                    ->success()
                    ->sendToDatabase($ticket->reporter);
            }
        }

        if ($ticket->isDirty('assigned_to')) {
            $oldTech = $ticket->getOriginal('assigned_to');
            $newTech = $ticket->assigned_to;

            $ticket->logs()->create([
                'user_id' => Auth::id(),
                'action' => 'technician_assigned',
                'notes' => "Teknisi berubah dari ID {$oldTech} ke ID {$newTech}",
            ]);

        Notification::make()
            ->title('Tiket baru ditugaskan')
            ->body("Kamu telah ditugaskan untuk tiket '{$ticket->title}'.")
            ->warning()
            ->sendToDatabase($ticket->technician);
        }
    });
    }


    // ==================== METHODS ====================
    
    public function assignTo(User $technician)
    {
        $this->update([
            'assigned_to' => $technician->id,
            'status' => 'in_progress',
        ]);
    }

    public function markAsResolved()
    {
        $this->update([
            'status' => 'resolved',
            'resolved_at' => now(),
        ]);
    }

    public function markAsClosed()
    {
        $this->update([
            'status' => 'closed',
        ]);
    }

    public function getStatusBadgeColor()
    {
        return match($this->status) {
            'open' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabel()
    {
        return match($this->status) {
            'open' => 'Open',
            'in_progress' => 'In Progress',
            'resolved' => 'Resolved',
            'closed' => 'Closed',
            default => $this->status,
        };
    }

    // ==================== ACCESSORS ====================

    public function getFloorAttribute()
    {
        return $this->accessPoint?->floor ?? '-';
    }

}