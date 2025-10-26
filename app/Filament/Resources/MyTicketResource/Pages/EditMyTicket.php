<?php

namespace App\Filament\Resources\MyTicketResource\Pages;

use App\Filament\Resources\MyTicketResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditMyTicket extends EditRecord
{
    protected static string $resource = MyTicketResource::class;

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
