<?php

namespace App\Filament\Resources\ExcursionSlots\Pages;

use App\Filament\Resources\ExcursionSlots\ExcursionSlotResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditExcursionSlot extends EditRecord
{
    protected static string $resource = ExcursionSlotResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
