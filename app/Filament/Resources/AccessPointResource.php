<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessPointResource\Pages;
use App\Filament\Resources\AccessPointResource\RelationManagers;
use App\Models\AccessPoint;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class AccessPointResource extends Resource
{
    protected static ?string $model = AccessPoint::class;

    protected static ?string $navigationIcon = 'heroicon-o-wifi';
    protected static ?string $navigationLabel = 'Access Point';
    protected static ?string $navigationGroup = 'Manajemen Jaringan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('building_id')
                    ->label('Gedung')
                    ->relationship('building', 'name')
                    ->required()
                    ->preload()
                    ->searchable(),
                Forms\Components\Select::make('floor')
                    ->label('Lantai')
                    ->options([
                        1 => 'Lantai 1',
                        2 => 'Lantai 2',
                        3 => 'Lantai 3',
                        4 => 'Lantai 4',
                    ])
                    ->default(1)
                    ->required(),
                Forms\Components\Select::make('room_id')
                    ->label('Ruangan')
                    ->options(function (callable $get) {
                        $buildingId = $get('building_id');
                        if (!$buildingId) {
                            return[];
                        }
                        return \App\Models\Room::where('building_id', $buildingId)
                            ->pluck('name','id');
                    })
                    ->required()
                    ->reactive()
                    ->searchable(),
                Forms\Components\TextInput::make('name')
                    ->label('Nama AP')
                    ->required()
                    ->maxLength(100),
                Forms\Components\TextInput::make('mac_address')
                    ->label('Mac Address')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(50),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('x_position')
                            ->numeric()
                            ->label('posisi X')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('y_position')
                            ->numeric()
                            ->label('posisi Y')
                            ->default(0)
                            ->required(),
                    ]),
                Forms\Components\TextInput::make('signal_strength')
                    ->numeric()
                    ->label('Kekuatan Sinyal (dBm)')
                    ->default(-60),

                Forms\Components\Select::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Aktif',
                        'offline' => 'Offline',
                        'maintenance' => 'Perawatan',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama AP')->searchable(),
                Tables\Columns\TextColumn::make('mac_address')->label('MAC Address'),
                Tables\Columns\TextColumn::make('building.name')->label('Gedung')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('floor')->label('Lantai')->sortable(),
                Tables\Columns\TextColumn::make('room.name')->label('Ruangan')->searchable(),
                Tables\Columns\TextColumn::make('signal_strength')->label('Sinyal (dBm)'),
                Tables\Columns\TextColumn::make('status')
                    ->badge()
                    ->label('Status')
                    ->colors([
                        'success' => 'active',
                        'warning' => 'maintenance',
                        'danger' => 'offline',
                    ]),
                Tables\Columns\TextColumn::make('x_position')->label('X'),
                Tables\Columns\TextColumn::make('y_position')->label('Y'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListAccessPoints::route('/'),
            'create' => Pages\CreateAccessPoint::route('/create'),
            'edit' => Pages\EditAccessPoint::route('/{record}/edit'),
        ];
    }
}
