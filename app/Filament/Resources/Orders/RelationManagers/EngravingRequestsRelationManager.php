<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\EngravingRequests\EngravingRequestResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class EngravingRequestsRelationManager extends RelationManager
{
    protected static string $relationship = 'engravingRequests';

    protected static ?string $title = 'Engraving';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return EngravingRequestResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return EngravingRequestResource::table($table)->headerActions([]);
    }
}
