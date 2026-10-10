<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class AddressesRelationManager extends RelationManager
{
    protected static string $relationship = 'addresses';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('label')->badge(),
            TextColumn::make('name')->description(fn ($r) => $r->phone),
            TextColumn::make('full_address')->label('Address')->wrap(),
            IconColumn::make('is_default')->boolean(),
        ]);
    }
}
