<?php

namespace App\Filament\Widgets;

use App\Models\Ticket;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class RecentTicketsWidget extends BaseWidget
{
    protected static ?string $heading = 'Tiket Terbaru';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                Ticket::query()
                    ->with(['accessPoint', 'reporter'])
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Tiket')
                    ->searchable()
                    ->limit(50)
                    ->wrap(),
                    
                Tables\Columns\TextColumn::make('accessPoint.name')
                    ->label('Access Point')
                    ->badge()
                    ->color('info'),
                    
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'primary' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                    ])
                    ->formatStateUsing(fn (string $state): string => match($state) {
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                        default => $state,
                    }),
                    
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y, H:i')
                    ->sortable()
                    ->color('gray'),
            ])
            ->defaultSort('created_at', 'desc')
            ->paginated(false);
    }
}