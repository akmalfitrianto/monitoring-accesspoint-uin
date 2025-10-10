<?php

namespace App\Filament\Resources\BuildingResource\Pages;

use App\Filament\Resources\BuildingResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateBuilding extends CreateRecord
{
    protected static string $resource = BuildingResource::class;

    protected function getRedirectUrl(): string
    {
        // redirect ke halaman index setelah berhasil create
        return $this->getResource()::getUrl('index');
    }
}
