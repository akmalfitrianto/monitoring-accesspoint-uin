<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TicketNotification extends Notification
{
    use Queueable;

    public function __construct(
        public string $title,
        public string $body,
        public string $type = 'info',
        public ?string $url = null,
    )
    {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

  
    public function toDatabase(object $notifiable): array
    {
        return [
            'actions' => [],
            'body' => $this->body,
            'color' => $this->type,
            'duration' => 'persistent', 
            'icon' => $this->getIcon(),
            'iconColor' => $this->mapColor(),
            'status' => $this->mapColor(),
            'title' => $this->title,
            'view' => 'filament-notifications::notification',
            'viewdata' => [],
            'format' => 'filament'
        ];
    }

    public function toArray(object $notifiable): array
    {
        return $this->toDatabase($notifiable);
    }

    private function mapColor(): string
    {
        return match($this->type) {
            'success' => 'success',
            'warning' => 'warning',
            'danger' => 'danger',
            default => 'info',
        };
    }

    private function getIcon(): string
    {
        return match($this->type) {
            'success' => 'heroicon-o-check-circle',
            'warning' => 'heroicon-o-exclamation-triangle',
            'danger' => 'heroicon-o-x-circle',
            default => 'heroicon-o-information-circle',
        };
    }
}
