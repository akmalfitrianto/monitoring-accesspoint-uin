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
                    'backgroundColor' => [
                        'rgba(34, 197, 94, 0.7)',
                        'rgba(239, 68, 68, 0.7)',
                        'rgba(250, 204, 21, 0.7)',
                    ],
                    'borderColor' => [
                        'rgb(34, 197, 94)',
                        'rgb(239, 68, 68)',
                        'rgb(250, 204, 21)',
                    ],
                    'borderWidth' => 2,
                    'borderRadius' => 8, 
                    'hoverBackgroundColor' => [
                        'rgba(34, 197, 94, 0.9)',
                        'rgba(239, 68, 68, 0.9)',
                        'rgba(250, 204, 21, 0.9)',
                    ],
                ],    
            ],
            'labels' => ['Active', 'Offline', 'Maintenance'],
        ];
    }

    protected function getType(): string
    {
        return 'bar'; //bisa diganti dengan 'pie' atau 'doughnut' dll
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false, // Hide legend kalau gak perlu
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 0.1)', // Grid lines transparan
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false, // Hide grid vertical
                    ],
                ],
            ],
        ];
    }
}
