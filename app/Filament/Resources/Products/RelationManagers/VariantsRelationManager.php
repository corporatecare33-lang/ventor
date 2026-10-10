<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Enums\OptionType;
use App\Enums\StockMovementType;
use App\Models\ProductOption;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class VariantsRelationManager extends RelationManager
{
    protected static string $relationship = 'variants';

    protected static ?string $title = 'Variants (color / material / size)';

    public function form(Schema $schema): Schema
    {
        $options = fn (OptionType $type) => ProductOption::ofType($type)->pluck('name', 'id');

        return $schema->columns(3)->components([
            Select::make('color_id')->label('Color')->options($options(OptionType::Color))->searchable(),
            Select::make('material_id')->label('Material')->options($options(OptionType::Material))->searchable(),
            Select::make('size_id')->label('Size')->options($options(OptionType::Size))->searchable(),
            TextInput::make('sku')->label('SKU')->required()->maxLength(64)->unique(ignoreRecord: true)
                ->default(fn () => $this->getOwnerRecord()->sku.'-'),
            TextInput::make('price')->numeric()->prefix('৳')->helperText('Empty = product price'),
            TextInput::make('sale_price')->numeric()->prefix('৳'),
            TextInput::make('stock_quantity')->numeric()->integer()->minValue(0)->default(0)
                ->disabledOn('edit')->helperText(fn (string $operation) => $operation === 'edit' ? 'Use "Adjust stock" on the product.' : 'Opening stock'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true)->inline(false),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['color', 'material', 'size']))
            ->reorderable('sort_order')
            ->columns([
                ColorColumn::make('color.value')->label(''),
                TextColumn::make('color.name')->label('Color'),
                TextColumn::make('material.name')->label('Material')->toggleable(),
                TextColumn::make('size.name')->label('Size')->toggleable(),
                TextColumn::make('sku')->label('SKU')->searchable(),
                TextColumn::make('price')->money('BDT')->placeholder('Product price'),
                TextColumn::make('sale_price')->money('BDT')->placeholder('—'),
                TextColumn::make('stock_quantity')->label('Stock')->badge()
                    ->color(fn (int $state) => $state <= 0 ? 'danger' : ($state <= 5 ? 'warning' : 'success')),
                IconColumn::make('is_active')->boolean(),
            ])
            ->headerActions([
                CreateAction::make()->after(function (ProductVariant $record) {
                    if ($record->stock_quantity > 0) {
                        StockMovement::create([
                            'product_id' => $record->product_id,
                            'product_variant_id' => $record->id,
                            'user_id' => auth()->id(),
                            'type' => StockMovementType::Restock,
                            'quantity' => $record->stock_quantity,
                            'balance_after' => $record->stock_quantity,
                            'note' => 'Opening variant stock',
                        ]);
                    }
                }),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
