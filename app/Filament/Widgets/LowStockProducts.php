<?php

namespace App\Filament\Widgets;

use App\Enums\ProductStatus;
use App\Filament\Resources\Products\ProductResource;
use App\Models\Product;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockProducts extends TableWidget
{
    protected static ?int $sort = 4;

    protected static ?string $heading = 'Low stock';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view catalog');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Product::query()->where('status', '!=', ProductStatus::Draft)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->orderBy('stock_quantity'))
            ->defaultPaginationPageOption(5)
            ->emptyStateHeading('All products are well stocked')
            ->columns([
                TextColumn::make('name')->description(fn (Product $r) => $r->sku),
                TextColumn::make('stock_quantity')->label('Stock')->badge()->color(fn (int $state) => $state <= 0 ? 'danger' : 'warning'),
            ])
            ->recordActions([
                Action::make('open')->url(fn (Product $r) => ProductResource::getUrl('edit', ['record' => $r])),
            ]);
    }
}
