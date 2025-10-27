<?php

namespace App\Filament\Resources;

use App\Filament\Resources\MyTicketResource\Pages;
use App\Models\Ticket;
use Filament\Resources\Resource;
use Filament\Facades\Filament;
use Filament\Forms;
use App\Models\Building;
use App\Models\Room;
use App\Models\AccessPoint;
use Filament\Forms\Form;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Actions\Action;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;

class MyTicketResource extends Resource
{
    protected static ?string $model = Ticket::class;
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?string $navigationGroup = 'Ticketing';
    protected static ?string $navigationLabel = 'My Tickets';

    public static function form(Form $form): Form
{
    return $form
        ->schema([
            Forms\Components\TextInput::make('title')
                ->label('Judul Masalah')
                ->required()
                ->maxLength(255),

            Forms\Components\Textarea::make('description')
                ->label('Deskripsi Masalah')
                ->required()
                ->rows(4),

            Forms\Components\Select::make('building_id')
                ->label('Gedung')
                ->options(Building::pluck('name', 'id'))
                ->required()
                ->reactive()
                ->searchable(),

            //  Dropdown untuk memilih lantai
            Forms\Components\Select::make('floor')
                ->label('Lantai')
                ->options(function (callable $get) {
                    $buildingId = $get('building_id');
                    if (!$buildingId) return [];

                    // Ambil daftar lantai unik dari Room di building tsb
                    return Room::where('building_id', $buildingId)
                        ->pluck('floor', 'floor')
                        ->unique()
                        ->sortKeys()
                        ->toArray();
                })
                ->required()
                ->reactive()
                ->searchable(),

            Forms\Components\Select::make('room_id')
                ->label('Ruangan')
                ->options(function (callable $get) {
                    $buildingId = $get('building_id');
                    $floor = $get('floor');
                    if (!$buildingId || !$floor) return [];

                    return Room::where('building_id', $buildingId)
                        ->where('floor', $floor)
                        ->pluck('name', 'id');
                })
                ->required()
                ->reactive()
                ->searchable(),

            Forms\Components\Select::make('access_point_id')
                ->label('Access Point')
                ->options(function (callable $get) {
                    $roomId = $get('room_id');
                    if (!$roomId) return [];

                    return AccessPoint::where('room_id', $roomId)
                        ->pluck('name', 'id');
                })
                ->searchable()
                ->reactive(),
        ]);
}


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')->label('Judul')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('building.name')->label('Gedung')->sortable(),
                Tables\Columns\TextColumn::make('room.name')->label('Ruangan')->default('-'),
                Tables\Columns\TextColumn::make('accessPoint.name')->label('Access Point')->default('-'),
                Tables\Columns\TextColumn::make('floor')->label('Lantai')->sortable(),
                Tables\Columns\TextColumn::make('reporter.name')->label('Pelapor')->sortable(),
                Tables\Columns\TextColumn::make('technician.name')->label('Teknisi')->default('-')->sortable(),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'primary' => 'open',
                        'warning' => 'in_progress',
                        'success' => 'resolved',
                        'secondary' => 'closed',
                    ]),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime('d M Y H:i')
                    ->sortable(),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make()
                    ->visible(fn () => optional(Auth::user())->hasRole(['admin', 'teknisi'])),

                Action::make('set_in_progress')
                    ->label('In Progress')
                    ->icon('heroicon-o-wrench-screwdriver')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['open']) && optional(Auth::user())->hasRole(['admin', 'teknisi']))
                    ->action(fn ($record) => $record->update(['status' => 'in_progress'])),

                Action::make('set_resolved')
                    ->label('Resolved')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['in_progress']) && optional(Auth::user())->hasRole(['admin', 'teknisi']))
                    ->action(fn ($record) => $record->update([
                        'status' => 'resolved',
                        'resolved_at' => now(),
                    ])),

                Action::make('set_closed')
                    ->label('Closed')
                    ->icon('heroicon-o-x-circle')
                    ->color('gray')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => in_array($record->status, ['resolved']) && optional(Auth::user())->hasRole(['admin', 'teknisi']))
                    ->action(fn ($record) => $record->update(['status' => 'closed'])),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make()
                    ->visible(fn () => optional(Auth::user())->hasRole('admin')),
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        $user = filament()->auth()->user();

        if($user && $user->hasRole('user')){
            return parent::getEloquentQuery()
                ->where('reported_by', $user->id);
        }

        return parent::getEloquentQuery();
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListMyTickets::route('/'),
            'create' => Pages\CreateMyTicket::route('/create'),
        ];
    }
    
    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if(!$user) return false;

        return $user->HasAnyRole(['user', 'admin', 'teknisi']);
    }
}