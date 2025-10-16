<?php

namespace App\Filament\Resources;

use App\Filament\Resources\BuildingResource\Pages;
use App\Filament\Resources\BuildingResource\RelationManagers;
use App\Models\Building;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class BuildingResource extends Resource
{
    protected static ?string $model = Building::class;

    protected static ?string $navigationIcon = 'heroicon-o-building-office';
    protected static ?string $navigationLabel = 'Gedung';
    protected static ?string $navigationGroup = 'Manajemen Jaringan';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('name')
                    ->label('Nama Gedung')
                    ->required()
                    ->maxLength(255),
                Forms\Components\TextInput::make('code')
                    ->label('Kode Gedung')
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(255),
                Forms\Components\TextInput::make('total_floors')
                    ->label('Total Lantai')
                    ->numeric()
                    ->default(1)
                    ->minValue(1)
                    ->maxValue(10)
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('Deskripsi Gedung')
                    ->rows(2),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('grid_width')
                            ->numeric()
                            ->label('Lebar Grid')
                            ->default(0)
                            ->required(),
                        Forms\Components\TextInput::make('grid_height')
                            ->numeric()
                            ->label('Tinggi Grid')
                            ->default(0)
                            ->required(),
                    ]),
                Forms\Components\Grid::make(2)
                    ->schema([
                        Forms\Components\TextInput::make('x_position')
                            ->numeric()
                            ->label('Posisi X')
                            ->default(0)
                            ->required(),

                        Forms\Components\TextInput::make('y_position')
                            ->numeric()
                            ->label('Posisi Y')
                            ->default(0)
                            ->required(),
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')->label('Nama Gedung')->searchable(),
                Tables\Columns\TextColumn::make('code')->label('Kode Gedung'),
                Tables\Columns\TextColumn::make('total_floors')->label('Total Lantai'),
                Tables\Columns\TextColumn::make('grid_width')->label('Lebar'),
                Tables\Columns\TextColumn::make('grid_height')->label('Tinggi'),
                Tables\Columns\TextColumn::make('x_position')->label('X'),
                Tables\Columns\TextColumn::make('y_position')->label('Y'),
                Tables\Columns\TextColumn::make('access_points_count')
                    ->label('Jumlah AP')
                    ->counts('accessPoints'),
                Tables\Columns\TextColumn::make('rooms_count')
                    ->label('Jumlah Ruangan')
                    ->counts('rooms'),
            ])
            ->filters([
                //
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
            ->withCount('accessPoints');
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
            'index' => Pages\ListBuildings::route('/'),
            'create' => Pages\CreateBuilding::route('/create'),
            'edit' => Pages\EditBuilding::route('/{record}/edit'),
        ];
    }
}
