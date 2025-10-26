<?php

namespace App\Filament\Resources\MyTicketResource\Pages;

use App\Filament\Resources\MyTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Auth;

class CreateMyTicket extends CreateRecord
{
    protected static string $resource = MyTicketResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Set pelapor otomatis
        $data['reported_by'] = Auth::id();
        $data['status'] = 'open';

        return $data;
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Tiket berhasil dibuat!';
    }
}
