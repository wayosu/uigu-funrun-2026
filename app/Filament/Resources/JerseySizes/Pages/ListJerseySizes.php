<?php

namespace App\Filament\Resources\JerseySizes\Pages;

use App\Filament\Resources\JerseySizes\JerseySizeResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListJerseySizes extends ListRecords
{
    protected static string $resource = JerseySizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
