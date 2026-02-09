<?php

namespace App\Filament\Resources\JerseySizes;

use App\Filament\Resources\JerseySizes\Pages\CreateJerseySize;
use App\Filament\Resources\JerseySizes\Pages\EditJerseySize;
use App\Filament\Resources\JerseySizes\Pages\ListJerseySizes;
use App\Filament\Resources\JerseySizes\Schemas\JerseySizeForm;
use App\Filament\Resources\JerseySizes\Tables\JerseySizesTable;
use App\Models\JerseySize;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class JerseySizeResource extends Resource
{
    protected static ?string $model = JerseySize::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static \UnitEnum|string|null $navigationGroup = 'Event Configuration';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    public static function form(Schema $schema): Schema
    {
        return JerseySizeForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return JerseySizesTable::configure($table);
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
            'index' => ListJerseySizes::route('/'),
            'create' => CreateJerseySize::route('/create'),
            'edit' => EditJerseySize::route('/{record}/edit'),
        ];
    }
}
