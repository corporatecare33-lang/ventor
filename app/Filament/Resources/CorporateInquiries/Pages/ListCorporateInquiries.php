<?php

namespace App\Filament\Resources\CorporateInquiries\Pages;

use App\Filament\Resources\CorporateInquiries\CorporateInquiryResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCorporateInquiries extends ListRecords
{
    protected static string $resource = CorporateInquiryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
