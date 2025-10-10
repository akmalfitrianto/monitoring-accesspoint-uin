<?php

namespace App\Filament\Resources\AccessPointResource\Pages;

use App\Filament\Resources\AccessPointResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditAccessPoint extends EditRecord
{
    protected static string $resource = AccessPointResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
