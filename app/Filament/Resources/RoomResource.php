<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RoomResource\Pages;
use App\Filament\Resources\RoomResource\RelationManagers;
use App\Models\Room;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RoomResource extends Resource
{
    protected static ?string $model = Room::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationLabel = 'Ruangan';
    protected static ?string $navigationGroup = 'Manajemen Jaringan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Ruangan')
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
                        Forms\Components\TextInput::make('name')
                            ->label('Nama Ruangan')
                            ->required(),
                        Forms\Components\TextInput::make('code')
                            ->label('Kode Ruangan')
                            ->maxLength(50),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Posisi Ruangan')
                    ->schema([
                        Forms\Components\TextInput::make('x_position')
                            ->label('Posisi X')
                            ->numeric()
                            ->required(),

                        Forms\Components\TextInput::make('y_position')
                            ->label('Posisi Y')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Ukuran Ruangan')
                    ->schema([
                        Forms\Components\TextInput::make('width')
                            ->label('Lebar')
                            ->numeric()
                            ->required(),
                        Forms\Components\TextInput::make('height')
                            ->label('Tinggi')
                            ->numeric()
                            ->required(),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Nama Ruangan')
                    ->searchable(),
                Tables\Columns\TextColumn::make('building.name')
                    ->label('Gedung')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('floor')
                    ->label('Lantai')
                    ->sortable(),
                Tables\Columns\TextColumn::make('code')
                    ->label('Kode'),
                Tables\Columns\TextColumn::make('x_position')
                    ->label('X')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('y_position')
                    ->label('Y')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('width')
                    ->label('Lebar')
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('height')
                    ->label('Tinggi')
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('building_id')
                ->label('Filter Gedung')
                ->options(
                    \App\Models\Building::pluck('name', 'id')->toArray()
                )
                ->placeholder('Semua Gedung')
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
            'index' => Pages\ListRooms::route('/'),
            'create' => Pages\CreateRoom::route('/create'),
            'edit' => Pages\EditRoom::route('/{record}/edit'),
        ];
    }

    public static function shouldRegisterNavigation(): bool
    {
        $user = auth()->user();

        if (!$user) return false;

        return $user->hasRole('superadmin');
    }

}
