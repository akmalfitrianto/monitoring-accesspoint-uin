<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TicketResource\Pages;
use App\Filament\Resources\TicketResource\RelationManagers;
use App\Models\Ticket;
use App\Models\Building;
use App\Models\Room;
use Filament\Resources\Pages\ViewRecord;
use App\Models\AccessPoint;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TicketResource extends Resource
{
    protected static ?string $model = Ticket::class;

    protected static ?string $navigationLabel = 'All Ticketing';
    protected static ?string $navigationGroup = 'Ticketing';
    protected static ?string $navigationIcon = 'heroicon-o-ticket';
    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Tiket')
                    ->schema([
                        Forms\Components\TextInput::make('title')
                            ->label('Judul Masalah')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\Textarea::make('description')
                            ->label('Deskripsi Masalah')
                            ->required()
                            ->rows(4),

                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'open' => 'Open',
                                'in_progress' => 'In Progress',
                                'resolved' => 'Resolved',
                                'closed' => 'Closed',
                            ])
                            ->default('open')
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Lokasi')
                    ->schema([
                        Forms\Components\Select::make('building_id')
                            ->label('Gedung')
                            ->options(Building::pluck('name', 'id'))
                            ->required()
                            ->reactive()
                            ->searchable()
                            ->preload(),
                        
                        Forms\Components\Select::make('room_id')
                            ->label('Ruangan')
                            ->options(function(callable $get) {
                                $buildingId = $get('building_id');
                                if(!$buildingId) {
                                    return[];
                                }
                                return Room::where('building_id', $buildingId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable()
                            ->reactive(),

                        Forms\Components\Select::make('access_point_id')
                            ->label('Access Point')
                            ->options(function(callable $get){
                                $roomId = $get('room_id');
                                if(!$roomId) {
                                    return[];
                                }
                                return AccessPoint::where('room_id', $roomId)
                                    ->pluck('name', 'id');
                            })
                            ->searchable(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Assignment')
                    ->schema([
                        Forms\Components\Select::make('reported_by')
                            ->label('Dilaporkan Oleh')
                            ->options(User::pluck('name', 'id'))
                            ->required()
                            ->searchable(),
                        
                        Forms\Components\Select::make('assigned_to')
                            ->label('Ditugaskan Kepada')
                            ->options(User::role('teknisi')->pluck('name', 'id'))
                            ->searchable()
                            ->placeholder('Belum Ditugaskan'),

                        Forms\Components\DateTimePicker::make('resolved_at')
                            ->label('Waktu Selesai')
                            ->placeholder('Belum Selesai')
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('title')
                    ->label('Judul')
                    ->searchable()
                    ->limit(30),
                Tables\Columns\SelectColumn::make('status')
                    ->label('Status')
                    ->options([
                        'open' => 'Open',
                        'in_progress' => 'In Progress',
                        'resolved' => 'Resolved',
                        'closed' => 'Closed',
                    ]),
                Tables\Columns\TextColumn::make('building.name')
                    ->label('Gedung')
                    ->searchable(),
                Tables\Columns\TextColumn::make('accessPoint.name')
                    ->label('Access Point')
                    ->searchable(),
                Tables\Columns\TextColumn::make('reporter.name')
                    ->label('Pelapor')
                    ->searchable(),
                Tables\Columns\TextColumn::make('technician.name')
                    ->label('Teknisi')
                    ->searchable()
                    ->placeholder('Belum ditugaskan'),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Dibuat')
                    ->dateTime(' d M Y H:i ')
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
                Tables\Filters\SelectFilter::make('building_id')
                    ->label('Gedung')
                    ->options(Building::pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListTickets::route('/'),
            'create' => Pages\CreateTicket::route('/create'),
            'edit' => Pages\EditTicket::route('/{record}/edit'),
            'view' => Pages\ViewTicket::route('/{record}'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        return $user->hasAnyRole(['admin', 'teknisi']);
    }

}
