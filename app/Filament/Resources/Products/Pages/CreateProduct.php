<?php

namespace App\Filament\Resources\Products\Pages;

use App\Enums\StockMovementType;
use App\Filament\Resources\Products\ProductResource;
use App\Models\StockMovement;
use Filament\Resources\Pages\CreateRecord;

class CreateProduct extends CreateRecord
{
    protected static string $resource = ProductResource::class;

    protected function afterCreate(): void
    {
        if ($this->record->stock_quantity > 0) {
            StockMovement::create([
                'product_id' => $this->record->id,
                'user_id' => auth()->id(),
                'type' => StockMovementType::Restock,
                'quantity' => $this->record->stock_quantity,
                'balance_after' => $this->record->stock_quantity,
                'note' => 'Opening stock',
            ]);
        }
    }

    protected function getRedirectUrl(): string
    {
        return static::getResource()::getUrl('edit', ['record' => $this->record]);
    }
}
