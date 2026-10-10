<?php

namespace App\Filament\Resources\EngravingRequests\Pages;

use App\Filament\Resources\EngravingRequests\EngravingRequestResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListEngravingRequests extends ListRecords
{
    protected static string $resource = EngravingRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
