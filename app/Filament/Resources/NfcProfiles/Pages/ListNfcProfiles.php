<?php

namespace App\Filament\Resources\NfcProfiles\Pages;

use App\Filament\Resources\NfcProfiles\NfcProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNfcProfiles extends ListRecords
{
    protected static string $resource = NfcProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
