<?php

namespace App\Filament\Resources\Providers\Pages;

use App\Filament\Resources\Providers\ProviderResource;
use Filament\Resources\Pages\ManageRecords;

class ManageProviders extends ManageRecords
{
    protected static string $resource = ProviderResource::class;
}