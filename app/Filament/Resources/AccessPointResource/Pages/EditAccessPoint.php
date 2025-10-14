<?php

namespace App\Filament\Resources\AccessPointResource\Pages;

use App\Filament\Resources\AccessPointResource;
use Filament\Actions;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;

class EditAccessPoint extends EditRecord
{
    protected static string $resource = AccessPointResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getSavedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Access Point berhasil diperbarui')
            ->success();
    }


}
