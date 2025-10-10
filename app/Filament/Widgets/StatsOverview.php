<?php

namespace App\Filament\Widgets;

use App\Models\AccessPoint;
use App\Models\Building;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverview extends BaseWidget
{
    protected function getStats(): array
    {
        return [
            Stat::make('Total Gedung', Building::count())
                ->description('Jumlah semua gedung terdaftar')
                ->color('gray')
                ->icon('heroicon-o-building-office'),

            Stat::make('AP Aktif', AccessPoint::where('status', 'active')-> count()) 
                ->description('Jumlah Access Point aktif')
                ->color('success')
                ->icon('heroicon-o-wifi'),

            Stat::make('AP Offline', AccessPoint::where('status', 'offline')->count())
                ->description('Jumlah Access Point offline')
                ->color('danger')
                ->icon('heroicon-o-signal-slash'),

            Stat::make('Maintenance', AccessPoint::where('status', 'maintenance')->count())
                ->description('Jumlah AP dalam perawatan')
                ->color('warning')
                ->icon('heroicon-o-wrench-screwdriver'),
        ];
    }
}
