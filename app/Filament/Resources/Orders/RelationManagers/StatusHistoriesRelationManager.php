<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StatusHistoriesRelationManager extends RelationManager
{
    protected static string $relationship = 'statusHistories';

    protected static ?string $title = 'Timeline';

    public function isReadOnly(): bool
    {
        return true;
    }

    public function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user:id,name'))
            ->defaultSort('id', 'desc')
            ->paginated(false)
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y, h:i A'),
                TextColumn::make('status')->badge(),
                TextColumn::make('note')->wrap()->placeholder('—'),
                TextColumn::make('user.name')->label('By')->placeholder('Customer / System'),
            ]);
    }
}
