<?php

namespace App\Filament\Resources\JerseySizes\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class JerseySizesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Code')
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->weight('bold')
                    ->color('primary'),
                TextColumn::make('name')
                    ->label('Size Name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('raceCategory.name')
                    ->label('Race Category')
                    ->badge()
                    ->color('info')
                    ->default('Global')
                    ->sortable(),
                TextColumn::make('sort_order')
                    ->label('Order')
                    ->sortable()
                    ->alignCenter(),
                IconColumn::make('is_active')
                    ->label('Active')
                    ->boolean()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Created')
                    ->dateTime('d M Y')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('sort_order')
            ->filters([
                TernaryFilter::make('is_active')
                    ->label('Active Status')
                    ->placeholder('All sizes')
                    ->trueLabel('Active only')
                    ->falseLabel('Inactive only'),
                SelectFilter::make('race_category_id')
                    ->label('Race Category')
                    ->relationship('raceCategory', 'name')
                    ->searchable()
                    ->preload(),
                TernaryFilter::make('global')
                    ->label('Scope')
                    ->placeholder('All')
                    ->trueLabel('Global only')
                    ->falseLabel('Category-specific only')
                    ->queries(
                        true: fn ($query) => $query->whereNull('race_category_id'),
                        false: fn ($query) => $query->whereNotNull('race_category_id'),
                    ),
            ])
            ->recordActions([
                EditAction::make()
                    ->icon(Heroicon::OutlinedPencilSquare),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
