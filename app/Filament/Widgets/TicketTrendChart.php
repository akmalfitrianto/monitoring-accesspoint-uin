<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Widgets\ChartWidget;
use Carbon\Carbon;

class TicketTrendChart extends ChartWidget
{
    protected static ?string $heading = 'Tren Tiket (7 Hari Terakhir)';
    protected static ?int $sort = 2;
    protected static ?string $pollingInterval = '30s';

    protected function getData(): array
    {
        $days = collect(range(6, 0))->map(function ($daysAgo) {
            $date = Carbon::now()->subDays($daysAgo);
            
            return [
                'date' => $date->format('d M'),
                'open' => Ticket::whereDate('created_at', $date)
                    ->whereIn('status', ['open', 'in_progress'])
                    ->count(),
                'resolved' => Ticket::whereDate('created_at', $date)
                    ->where('status', 'resolved')
                    ->count(),
            ];
        });

        return [
            'datasets' => [
                [
                    'label' => 'Tiket Dibuat',
                    'data' => $days->pluck('open')->toArray(),
                    'backgroundColor' => 'rgba(239, 68, 68, 0.6)',
                    'borderColor' => 'rgb(239, 68, 68)',
                    'borderWidth' => 2,
                    'tension' => 0.4, // Smooth curve
                ],
                [
                    'label' => 'Tiket Selesai',
                    'data' => $days->pluck('resolved')->toArray(),
                    'backgroundColor' => 'rgba(34, 197, 94, 0.6)',
                    'borderColor' => 'rgb(34, 197, 94)',
                    'borderWidth' => 2,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $days->pluck('date')->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'plugins' => [
                'legend' => [
                    'display' => false,
                    'position' => 'bottom',
                ],
            ],
            'scales' => [
                'y' => [
                    'beginAtZero' => true,
                    'ticks' => [
                        'stepSize' => 1, // Increment by 1
                    ],
                    'grid' => [
                        'color' => 'rgba(255, 255, 255, 0.1)',
                    ],
                ],
                'x' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
            'interaction' => [
                'intersect' => false,
                'mode' => 'index',
            ],
        ];
    }
}