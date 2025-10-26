<?php

namespace App\Filament\Resources\MyTicketResource\Pages;

use App\Filament\Resources\MyTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListMyTickets extends ListRecords
{
    protected static string $resource = MyTicketResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->label('Buat Tiket Baru')
                ->icon('heroicon-o-plus-circle'),
        ];
    }

    public function getTitle(): string
    {
        return 'Daftar Semua Ticket';
    }
}
