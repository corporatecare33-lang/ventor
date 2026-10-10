<?php

namespace App\Filament\Resources\Products\RelationManagers;

use App\Filament\Resources\Reviews\ReviewResource;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class ReviewsRelationManager extends RelationManager
{
    protected static string $relationship = 'reviews';

    public function form(Schema $schema): Schema
    {
        return ReviewResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return ReviewResource::table($table);
    }
}
