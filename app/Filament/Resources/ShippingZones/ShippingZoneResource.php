<?php

namespace App\Filament\Resources\ShippingZones;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\ShippingZones\Pages\ListShippingZones;
use App\Models\ShippingZone;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class ShippingZoneResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = ShippingZone::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Shipping / delivery';

    protected static function permissionArea(): string
    {
        return 'settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(100)->placeholder('Inside Dhaka'),
            TextInput::make('description')->maxLength(255),
            TextInput::make('charge')->numeric()->prefix('৳')->required()->minValue(0),
            TextInput::make('free_shipping_threshold')->numeric()->prefix('৳')->helperText('Free delivery when subtotal reaches this amount. Empty = never.'),
            TextInput::make('min_days')->numeric()->integer()->required()->default(1)->suffix('days'),
            TextInput::make('max_days')->numeric()->integer()->required()->default(3)->suffix('days')->gte('min_days'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true)->inline(false),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('name')->weight('medium')->description(fn (ShippingZone $r) => $r->description),
                TextColumn::make('charge')->money('BDT'),
                TextColumn::make('free_shipping_threshold')->label('Free over')->money('BDT')->placeholder('—'),
                TextColumn::make('min_days')->label('Delivery time')->formatStateUsing(fn (ShippingZone $r) => $r->estimate()),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListShippingZones::route('/')];
    }
}
