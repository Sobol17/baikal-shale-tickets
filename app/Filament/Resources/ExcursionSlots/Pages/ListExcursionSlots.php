<?php

namespace App\Filament\Resources\ExcursionSlots\Pages;

use App\Filament\Resources\ExcursionSlots\ExcursionSlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListExcursionSlots extends ListRecords
{
    protected static string $resource = ExcursionSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
