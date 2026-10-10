<?php

namespace App\Filament\Resources\Coupons;

use App\Enums\CouponType;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Coupons\Pages\ListCoupons;
use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class CouponResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 1;

    protected static function permissionArea(): string
    {
        return 'marketing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('code')->required()->maxLength(40)->unique(ignoreRecord: true)->alphaDash()
                ->extraInputAttributes(['style' => 'text-transform:uppercase']),
            TextInput::make('description')->maxLength(255),
            Select::make('type')->options(CouponType::class)->default(CouponType::Fixed)->required()->live(),
            TextInput::make('value')->numeric()->required()->minValue(0)
                ->suffix(fn ($get) => in_array($get('type'), [CouponType::Percent, 'percent'], true) ? '%' : '৳')
                ->maxValue(fn ($get) => in_array($get('type'), [CouponType::Percent, 'percent'], true) ? 100 : null),
            TextInput::make('min_order_amount')->numeric()->prefix('৳'),
            TextInput::make('max_discount')->numeric()->prefix('৳')->helperText('Cap for percentage coupons'),
            TextInput::make('usage_limit')->numeric()->integer()->helperText('Empty = unlimited'),
            TextInput::make('used_count')->numeric()->disabled()->dehydrated(false)->default(0),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('expires_at')->after('starts_at'),
            Toggle::make('free_shipping'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('code')->searchable()->weight('bold')->copyable()->description(fn (Coupon $record) => $record->description),
                TextColumn::make('value')->formatStateUsing(fn (Coupon $record) => $record->type === CouponType::Percent ? rtrim(rtrim((string) $record->value, '0'), '.').'%' : money($record->value)),
                TextColumn::make('min_order_amount')->label('Min order')->money('BDT')->placeholder('—'),
                TextColumn::make('used_count')->label('Used')->formatStateUsing(fn (Coupon $record) => $record->used_count.($record->usage_limit ? ' / '.$record->usage_limit : '')),
                IconColumn::make('free_shipping')->boolean(),
                TextColumn::make('expires_at')->dateTime('d M Y')->placeholder('Never')->color(fn (Coupon $record) => $record->expires_at?->isPast() ? 'danger' : null),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([TernaryFilter::make('is_active')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCoupons::route('/')];
    }
}
