<?php

namespace App\Filament\Resources\AvailabilitySlots\Pages;

use App\Filament\Resources\AvailabilitySlots\AvailabilitySlotResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ManageRecords;

class ManageAvailabilitySlots extends ManageRecords
{
    protected static string $resource = AvailabilitySlotResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
