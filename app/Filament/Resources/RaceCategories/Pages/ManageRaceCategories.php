<?php

namespace App\Filament\Resources\RaceCategories\Pages;

use App\Filament\Resources\RaceCategories\RaceCategoryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageRaceCategories extends ManageRecords
{
    protected static string $resource = RaceCategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
