<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Building;

class BuildingMap extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-map';
    protected static ?string $navigationLabel = 'Denah Gedung';
    protected static ?string $navigationGroup = 'Monitoring';
    protected static string $view = 'filament.pages.building-map';

    public $buildings;

    public function mount()
    {
        $this->buildings = Building::withCount('accessPoints')->get();
    }
}
