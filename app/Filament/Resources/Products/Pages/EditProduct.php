<?php

namespace App\Filament\Resources\Products\Pages;

use App\Filament\Resources\Products\ProductResource;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditProduct extends EditRecord
{
    protected static string $resource = ProductResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('view_on_site')->label('View on site')->color('gray')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                ->url(fn () => route('products.show', $this->record), shouldOpenInNewTab: true),
            ProductResource::adjustStockAction()->after(fn () => $this->refreshFormData(['stock_quantity', 'status'])),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }
}
