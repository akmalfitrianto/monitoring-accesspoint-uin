<?php

namespace App\Filament\Resources\TicketResource\Widgets;

use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Tables;
use App\Models\TicketLog;
use Illuminate\Database\Eloquent\Builder;

class TicketLogsWidget extends BaseWidget
{
    public ?int $recordId = null;

    protected function getTableQuery(): Builder
    {
        $query = TicketLog::query();

        if ($this->recordId) {
            $query->where('ticket_id', $this->recordId);
        } else {
            $query->whereRaw('1 = 0');
        }

        return $query->latest();
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('created_at')
                ->label('Tanggal')
                ->dateTime('d M Y H:i'),
            Tables\Columns\TextColumn::make('user.name')
                ->label('User'),
            Tables\Columns\TextColumn::make('action')
                ->label('Aksi'),
            Tables\Columns\TextColumn::make('notes')
                ->label('Catatan'),
        ];
    }

    protected function getHeading(): string
    {
        return 'Riwayat Perubahan Tiket';
    }
}
