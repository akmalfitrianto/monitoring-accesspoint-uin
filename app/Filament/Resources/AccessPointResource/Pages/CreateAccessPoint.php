<?php

namespace App\Filament\Resources\AccessPointResource\Pages;

use App\Filament\Resources\AccessPointResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateAccessPoint extends CreateRecord
{
    protected static string $resource = AccessPointResource::class;

    protected function getRedirectUrl(): string
    {
        // redirect ke halaman index setelah berhasil create
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Access Point berhasil ditambahkan')
            ->success();
    }
}
