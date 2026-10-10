<?php

namespace App\Filament\Resources\Payments;

use App\Enums\PaymentStatus;
use App\Enums\TransactionStatus;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\Payments\Pages\ListPayments;
use App\Models\Payment;
use App\Models\PaymentMethod;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Support\Facades\DB;
use UnitEnum;

/**
 * Manual payment verification. When bKash/Nagad/SSLCommerz APIs are connected later,
 * their callbacks should create/verify these same Payment records.
 */
class PaymentResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Payment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 2;

    protected static function permissionArea(): string
    {
        return 'orders';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('method')->options(fn () => PaymentMethod::orderBy('sort_order')->pluck('name', 'code'))->required(),
            TextInput::make('amount')->numeric()->prefix('৳')->required(),
            TextInput::make('transaction_id')->label('Transaction ID')->maxLength(100),
            TextInput::make('sender_number')->tel()->maxLength(20),
            Select::make('status')->options(TransactionStatus::class)->default(TransactionStatus::Pending)->required(),
            TextInput::make('note')->maxLength(255),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['order:id,order_number,customer_name', 'verifier:id,name']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')->label('Order')->searchable()
                    ->url(fn (Payment $record) => $record->order ? OrderResource::getUrl('view', ['record' => $record->order]) : null)
                    ->description(fn (Payment $record) => $record->order?->customer_name),
                TextColumn::make('method')->badge()->color('gray'),
                TextColumn::make('amount')->money('BDT'),
                TextColumn::make('transaction_id')->label('TrxID')->searchable()->copyable(),
                TextColumn::make('sender_number')->placeholder('—'),
                TextColumn::make('status')->badge(),
                TextColumn::make('verifier.name')->label('Verified by')->placeholder('—')->toggleable(),
                TextColumn::make('created_at')->since(),
            ])
            ->filters([
                SelectFilter::make('status')->options(TransactionStatus::class),
                SelectFilter::make('method')->options(fn () => PaymentMethod::pluck('name', 'code')),
            ])
            ->recordActions([
                Action::make('verify')->icon(Heroicon::OutlinedCheckBadge)->color('success')->requiresConfirmation()
                    ->modalDescription('Confirm you have received this amount in the merchant account. The order will be marked as paid.')
                    ->authorize(fn () => static::can('update'))
                    ->visible(fn (Payment $record) => $record->status === TransactionStatus::Pending)
                    ->action(fn (Payment $record) => DB::transaction(function () use ($record) {
                        $record->update(['status' => TransactionStatus::Verified, 'verified_by' => auth()->id(), 'verified_at' => now()]);
                        $record->order?->update(['payment_status' => PaymentStatus::Paid]);
                    })),
                Action::make('reject')->icon(Heroicon::OutlinedXCircle)->color('danger')->requiresConfirmation()
                    ->authorize(fn () => static::can('update'))
                    ->visible(fn (Payment $record) => $record->status === TransactionStatus::Pending)
                    ->action(fn (Payment $record) => DB::transaction(function () use ($record) {
                        $record->update(['status' => TransactionStatus::Failed, 'verified_by' => auth()->id(), 'verified_at' => now()]);
                        $record->order?->update(['payment_status' => PaymentStatus::Failed]);
                    })),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListPayments::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Payment::where('status', TransactionStatus::Pending)->count();

        return $pending ? (string) $pending : null;
    }
}
