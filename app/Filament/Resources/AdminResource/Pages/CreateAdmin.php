<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Filament\Notifications\Notification;

class CreateAdmin extends CreateRecord
{
    protected static string $resource = AdminResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Simpan roles untuk digunakan setelah create
        $this->savedRoleId = $data['roles'] ?? null;
        
        // Hapus roles dari data yang akan disimpan ke tabel users
        unset($data['roles']);
        
        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->savedRoleId) {
            // syncRoles otomatis handle string atau array
            $this->record->assignRole($this->savedRoleId);
        }
    }   

    protected $savedRoleId = null;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Admin berhasil ditambahkan')
            ->success();
    }
}
