<?php

namespace App\Filament\Resources\JerseySizes\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Schema;

class JerseySizeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Grid::make(2)
                    ->schema([
                        TextInput::make('name')
                            ->label('Size Name')
                            ->required()
                            ->maxLength(255)
                            ->placeholder('e.g., Extra Small, Small, Medium'),
                        TextInput::make('code')
                            ->label('Size Code')
                            ->required()
                            ->unique(ignoreRecord: true)
                            ->maxLength(255)
                            ->placeholder('e.g., xs, s, m')
                            ->rules(['lowercase', 'alpha_dash']),
                        Select::make('race_category_id')
                            ->label('Race Category')
                            ->relationship('raceCategory', 'name')
                            ->searchable()
                            ->preload()
                            ->placeholder('Leave empty for global size')
                            ->helperText('If empty, this size will be available for all race categories'),
                        TextInput::make('sort_order')
                            ->label('Sort Order')
                            ->numeric()
                            ->default(0)
                            ->required()
                            ->helperText('Lower numbers appear first'),
                        Toggle::make('is_active')
                            ->label('Active')
                            ->default(true)
                            ->inline(false)
                            ->helperText('Only active sizes will be shown in registration form'),
                    ]),
            ]);
    }
}
