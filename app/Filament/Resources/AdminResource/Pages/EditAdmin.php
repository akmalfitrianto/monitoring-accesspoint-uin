<?php

namespace App\Filament\Resources\AdminResource\Pages;

use App\Filament\Resources\AdminResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Filament\Notifications\Notification;

class EditAdmin extends EditRecord
{
    protected static string $resource = AdminResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeFill(array $data): array
    {
        $roleId = $this->record->roles->first()?->id;

        if ($roleId) {
            $data['roles'] = $roleId;
        }

        return $data;
    }

     protected function mutateFormDataBeforeSave(array $data): array
    {
        // Simpan roles untuk digunakan setelah save
        $this->savedRoleId = $data['roles'] ?? null;
        
        // Hapus roles dari data yang akan disimpan ke tabel users
        unset($data['roles']);
        
        return $data;
    }

    protected function afterSave(): void
    {
        if ($this->savedRoleId){
            $role = \Spatie\Permission\Models\Role::find($this->savedRoleId);

            if ($role) {
                $this->record->syncRoles([$role]);
            }
        }
    }

    protected $savedRoleId = null;

    protected function getCreatedNotification(): ?Notification
    {
        return Notification::make()
            ->title('Data Admin berhasil diubah')
            ->success();
    }
}
