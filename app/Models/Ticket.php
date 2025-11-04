<?php

namespace App\Models;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use App\Notifications\TicketNotification;

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
                'user_id' => $ticket->reported_by,
                'action' => 'ticket_created',
                'new_status' => $ticket->status,
                'notes' => 'Tiket dibuat',
            ]);

            if ($ticket->reporter) {
                $ticket->reporter->notify(new TicketNotification(
                    title: 'Tiket baru dibuat',
                    body: "Tiket '{$ticket->title}' berhasil dilaporkan",
                    type: 'success'
                ));
            }

            $superAdmins = \App\Models\User::role(['superadmin'])->get();

            foreach ($superAdmins as $user) {
                $user->notify(new TicketNotification(
                    title: 'Tiket Baru Dibuat',
                    body: "{$ticket->reporter->name} membuat tiket baru: '{$ticket->title}'.",
                    type: 'info'
                ));
            }
        });

        static::updating(function ($ticket) {
            if ($ticket->isDirty('status')) {
                $old = $ticket->getOriginal('status');
                $new = $ticket->status;

                $updatingUserId = auth()->id() ?? $ticket->reported_by;

                $ticket->logs()->create([
                    'user_id' => $updatingUserId,
                    'action' => 'status_changed',
                    'old_status' => $old,
                    'new_status' => $new,
                    'notes' => "Status berubah dari {$old} menjadi {$new}",
                ]);

                if ($ticket->accessPoint) {
                    $apStatus = match($new) {
                        'resolved' => 'active',
                        'open' => 'maintenance',
                        default => $ticket->accessPoint->status
                    };
                    $ticket->accessPoint->update(['status' => $apStatus]);
                }

                if ($ticket->reporter && $ticket->reporter->id != $updatingUserId) {
                    $ticket->reporter->notify(new TicketNotification(
                        title: 'Status Tiket Berubah',
                        body: "Status tiket '{$ticket->title}' berubah dari {$old} menjadi {$new}",
                        type: 'success'
                    ));
                }

                $admins = \App\Models\User::role(['admin','superadmin'])
                    ->where('id', '!=', $updatingUserId)
                    ->get();
                
                foreach ($admins as $admin) {
                    $admin->notify(new TicketNotification(
                        title: 'Status tiket diperbarui',
                        body: "Status tiket '{$ticket->title}' diubah menjadi {$new}",
                        type: 'info'
                    ));
                }
            }
        });
    }


    // ==================== METHODS ====================

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
        return match ($this->status) {
            'open' => 'warning',
            'in_progress' => 'info',
            'resolved' => 'success',
            'closed' => 'gray',
            default => 'gray',
        };
    }

    public function getStatusLabel()
    {
        return match ($this->status) {
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
