<?php

namespace App\Filament\Resources\Carts;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Carts\Pages\ListCarts;
use App\Models\Cart;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use UnitEnum;

/** Read-only view of live and abandoned carts (useful for follow-up calls). */
class CartResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Cart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Carts';

    protected static function permissionArea(): string
    {
        return 'orders';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->components([
            TextEntry::make('user.name')->label('Customer')->placeholder('Guest'),
            TextEntry::make('coupon_code')->placeholder('—'),
            TextEntry::make('updated_at')->label('Last activity')->since(),
            RepeatableEntry::make('items')->columnSpanFull()->columns(3)->schema([
                TextEntry::make('product.name')->label('Product'),
                TextEntry::make('variant.sku')->label('Variant')->placeholder('—'),
                TextEntry::make('quantity'),
            ]),
        ])->columns(3);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('user:id,name,phone')->withCount('items')->withSum('items', 'quantity'))
            ->defaultSort('updated_at', 'desc')
            ->columns([
                TextColumn::make('id')->label('#'),
                TextColumn::make('user.name')->label('Customer')->placeholder('Guest')->description(fn (Cart $record) => $record->user?->phone),
                TextColumn::make('items_sum_quantity')->label('Units')->default(0),
                TextColumn::make('coupon_code')->placeholder('—'),
                TextColumn::make('converted_at')->label('Ordered')->since()->placeholder('Not ordered'),
                TextColumn::make('updated_at')->label('Last activity')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('converted')->label('Converted to order')
                    ->queries(true: fn ($query) => $query->whereNotNull('converted_at'), false: fn ($query) => $query->whereNull('converted_at')),
            ])
            ->recordActions([ViewAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCarts::route('/')];
    }
}
