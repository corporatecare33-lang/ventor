<?php

namespace App\Filament\Resources\Orders\RelationManagers;

use App\Filament\Resources\Payments\PaymentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Payment records';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return PaymentResource::form($schema);
    }

    public function table(Table $table): Table
    {
        return PaymentResource::table($table)
            ->headerActions([
                CreateAction::make()->label('Record payment')
                    ->mutateDataUsing(fn (array $data) => [...$data, 'amount' => $data['amount'] ?? $this->getOwnerRecord()->total]),
            ]);
    }
}
