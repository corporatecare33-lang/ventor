<?php

namespace App\Filament\Resources\StockMovements;

use App\Enums\StockMovementType;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\StockMovements\Pages\ListStockMovements;
use App\Models\StockMovement;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Forms\Components\DatePicker;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

class StockMovementResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = StockMovement::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArchiveBox;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Inventory log';

    protected static function permissionArea(): string
    {
        return 'catalog';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['product:id,name,sku', 'variant:id,sku', 'user:id,name']))
            ->defaultSort('id', 'desc')
            ->columns([
                TextColumn::make('created_at')->dateTime('d M Y, h:i A')->sortable(),
                TextColumn::make('product.name')->searchable()->description(fn (StockMovement $r) => $r->variant?->sku ?? $r->product?->sku),
                TextColumn::make('type')->badge(),
                TextColumn::make('quantity')->formatStateUsing(fn (int $state) => $state > 0 ? "+{$state}" : (string) $state)
                    ->color(fn (int $state) => $state > 0 ? 'success' : 'danger')->weight('bold'),
                TextColumn::make('balance_after')->label('Balance'),
                TextColumn::make('note')->wrap()->limit(60),
                TextColumn::make('user.name')->label('By')->placeholder('System'),
            ])
            ->filters([
                SelectFilter::make('type')->options(StockMovementType::class),
                Filter::make('created_at')->schema([DatePicker::make('from'), DatePicker::make('until')])
                    ->query(fn (Builder $q, array $data) => $q
                        ->when($data['from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListStockMovements::route('/')];
    }
}
