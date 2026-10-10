<?php

namespace App\Filament\Resources\PaymentMethods;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\PaymentMethods\Pages\ListPaymentMethods;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class PaymentMethodResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = PaymentMethod::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCreditCard;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    protected static ?int $navigationSort = 2;

    protected static function permissionArea(): string
    {
        return 'settings';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('code')->required()->alphaDash()->maxLength(40)->unique(ignoreRecord: true)->disabledOn('edit')
                ->helperText('Internal code, e.g. cod, bkash, nagad.'),
            TextInput::make('account_number')->label('Merchant / personal number')->maxLength(50),
            TextInput::make('sort_order')->numeric()->default(0),
            Textarea::make('instructions')->rows(3)->columnSpanFull()
                ->helperText('Shown at checkout, e.g. "Send Money to 01XXXXXXXXX and enter the TrxID".'),
            Toggle::make('requires_transaction_id')->label('Customer must enter transaction ID'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->description('Manual payment methods. Online gateways (bKash PGW, Nagad, SSLCommerz) need merchant API credentials — see docs/DEPLOYMENT.md.')
            ->columns([
                TextColumn::make('name')->weight('medium')->description(fn (PaymentMethod $record) => $record->code),
                TextColumn::make('account_number')->placeholder('—'),
                IconColumn::make('requires_transaction_id')->label('TrxID required')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPaymentMethods::route('/')];
    }
}
