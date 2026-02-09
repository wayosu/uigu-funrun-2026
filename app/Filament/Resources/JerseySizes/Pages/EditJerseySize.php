<?php

namespace App\Filament\Resources\JerseySizes\Pages;

use App\Filament\Resources\JerseySizes\JerseySizeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditJerseySize extends EditRecord
{
    protected static string $resource = JerseySizeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
