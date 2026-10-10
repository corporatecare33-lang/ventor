<?php

namespace App\Filament\Resources\Products\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsRelationManager extends RelationManager
{
    protected static string $relationship = 'stockMovements';

    protected static ?string $title = 'Stock history';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['variant.color', 'user:id,name']))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y, h:i A'),
                TextColumn::make('type')->badge(),
                TextColumn::make('variant.sku')->label('Variant')->placeholder('—'),
                TextColumn::make('quantity')->formatStateUsing(fn (int $state) => $state > 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger'),
                TextColumn::make('balance_after')->label('Balance'),
                TextColumn::make('note')->wrap(),
                TextColumn::make('user.name')->label('By')->placeholder('System'),
            ]);
    }
}
