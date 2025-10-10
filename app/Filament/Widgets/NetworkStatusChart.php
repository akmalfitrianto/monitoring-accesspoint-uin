<?php

namespace App\Filament\Widgets;

use App\Models\AccessPoint;
use Filament\Widgets\ChartWidget;

class NetworkStatusChart extends ChartWidget
{
    protected static ?string $heading = 'Status Jaringan Access Point';
    protected static ?string $pollingInterval = '10s';

    protected function getData(): array
    {
        $active = AccessPoint::where('status', 'active')->count();
        $offline = AccessPoint::where('status', 'offline')->count();
        $maintenance = AccessPoint::where('status', 'maintenance')->count();

        return [
            'datasets' => [
                [
                    'label' => 'Jumlah Access Point',
                    'data' => [$active, $offline, $maintenance],
                    'backgroundColor' => ['#22c55e', '#ef4444', '#facc15'],
                ],    
            ],
            'labels' => ['Active', 'Offline', 'Maintenance'],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; //bisa diganti dengan 'pie' atau 'doughnut' dll
    }
}
