<?php

namespace App\Filament\Resources\WarrantyRegistrations\Pages;

use App\Filament\Resources\WarrantyRegistrations\WarrantyRegistrationResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListWarrantyRegistrations extends ListRecords
{
    protected static string $resource = WarrantyRegistrationResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
