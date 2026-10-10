<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Orders\OrderResource;
use App\Models\Order;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LatestOrders extends TableWidget
{
    protected static ?int $sort = 3;

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return (bool) auth()->user()?->can('view orders');
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(Order::query()->latest()->limit(8))
            ->paginated(false)
            ->columns([
                TextColumn::make('order_number')->weight('bold'),
                TextColumn::make('customer_name')->description(fn (Order $r) => $r->phone),
                TextColumn::make('total')->money('BDT'),
                TextColumn::make('payment_method')->badge()->color('gray'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since(),
            ])
            ->recordActions([
                Action::make('open')->url(fn (Order $r) => OrderResource::getUrl('view', ['record' => $r])),
            ]);
    }
}
