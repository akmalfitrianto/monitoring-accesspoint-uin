<?php

namespace App\Filament\Resources;

use App\Filament\Resources\AccessPointResource\Pages;
use App\Filament\Resources\AccessPointResource\RelationManagers;
use App\Models\AccessPoint;
use App\Models\Building;
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
                Forms\Components\Section::make('Lokasi Access Point')
                    ->schema([
                        Forms\Components\Select::make('building_id')
                            ->label('Gedung')
                            ->relationship('building', 'name')
                            ->required()
                            ->preload()
                            ->searchable()
                            ->reactive(),
                        Forms\Components\Select::make('floor')
                            ->label('Lantai')
                            ->options(function (callable $get) {
                                $buildingId = $get('building_id');
                                if (!$buildingId) {
                                    return [];
                                }

                                $building = \App\Models\Building::find($buildingId);
                                if (!$building) {
                                    return [];
                                }

                                return collect(range(1, (int) $building->total_floors ?? 1))
                                    ->mapWithKeys(fn ($i) => [$i => "Lantai {$i}"]);
                            })
                            ->required()
                            ->reactive(),
                        Forms\Components\Select::make('room_id')
                            ->label('Ruangan')
                            ->options(function (callable $get) {
                                $buildingId = $get('building_id');
                                $floor = $get('floor');

                                if (!$buildingId || !$floor) {
                                    return[];
                                }
                                return \App\Models\Room::where('building_id', $buildingId)
                                    ->where('floor', $floor)
                                    ->pluck('name','id');
                            })
                            ->required()
                            ->reactive()
                            ->searchable(),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Access Point')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Nama AP')
                            ->required()
                            ->maxLength(100),
                        Forms\Components\TextInput::make('mac_address')
                            ->label('Mac Address')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(50),
                    ])
                    ->columns(2),
                Forms\Components\Section::make('Posisi Access Point')
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
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Status Access Points')
                    ->schema([
                        Forms\Components\Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Aktif',
                                'offline' => 'Offline',
                                'maintenance' => 'Perawatan',
                            ])
                            ->default('active')
                            ->required(),
                    ])
                    ->columns(1)
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->badge()
                    ->color('info')
                    ->label('Nama AP')
                    ->searchable(),
                Tables\Columns\TextColumn::make('mac_address')->label('MAC Address'),
                Tables\Columns\TextColumn::make('building.name')->label('Gedung')->searchable()->sortable(),
                Tables\Columns\TextColumn::make('floor')->label('Lantai')->sortable(),
                Tables\Columns\TextColumn::make('room.name')->label('Ruangan')->searchable(),
                Tables\Columns\TextColumn::make('status')->label('Status'),
                Tables\Columns\TextColumn::make('x_position')->label('X')->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('y_position')->label('Y')->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label('Status')
                    ->options([
                        'active' => 'Active',
                        'maintenance' => 'Maintenance',
                        'offline' => 'Offline',
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
            ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->with(['building','room'])
            ->withoutGlobalScopes();
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

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        return $user->hasRole('superadmin');
    }

}
