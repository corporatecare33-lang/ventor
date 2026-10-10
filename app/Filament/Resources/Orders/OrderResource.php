<?php

namespace App\Filament\Resources\Orders;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Orders\Pages\EditOrder;
use App\Filament\Resources\Orders\Pages\ListOrders;
use App\Filament\Resources\Orders\Pages\ViewOrder;
use App\Filament\Resources\Orders\RelationManagers\EngravingRequestsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\Orders\RelationManagers\StatusHistoriesRelationManager;
use App\Models\Order;
use App\Services\OrderService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class OrderResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Order::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingCart;

    protected static string|UnitEnum|null $navigationGroup = 'Sales';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'order_number';

    protected static function permissionArea(): string
    {
        return 'orders';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['order_number', 'phone', 'customer_name'];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    /** Edit is for address / courier / notes only; status changes go through the status action. */
    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('Customer & delivery address')->columns(2)->schema([
                TextInput::make('customer_name')->required()->maxLength(255),
                TextInput::make('phone')->required()->tel()->maxLength(20),
                TextInput::make('email')->email(),
                TextInput::make('address')->required()->columnSpanFull(),
                TextInput::make('area'),
                TextInput::make('city')->required(),
                TextInput::make('district'),
                TextInput::make('postal_code'),
            ]),
            Section::make('Courier & notes')->schema([
                TextInput::make('courier_name')->placeholder('Steadfast / Pathao / RedX / Sundarban'),
                TextInput::make('tracking_number'),
                Textarea::make('admin_note')->rows(4)->helperText('Internal only.'),
            ]),
        ]);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make('Items')->schema([
                    RepeatableEntry::make('items')->hiddenLabel()->columns(5)->schema([
                        TextEntry::make('product_name')->label('Product')->columnSpan(2)
                            ->helperText(fn ($record) => trim(($record->variant_name ? $record->variant_name.' · ' : '').$record->sku)),
                        TextEntry::make('unit_price')->money('BDT'),
                        TextEntry::make('quantity')->label('Qty'),
                        TextEntry::make('line_total')->money('BDT')->weight('bold')
                            ->helperText(fn ($record) => (float) $record->engraving_fee > 0 ? 'incl. engraving '.money($record->engraving_fee) : null),
                    ]),
                ]),
                Section::make('Totals')->columns(5)->schema([
                    TextEntry::make('subtotal')->money('BDT'),
                    TextEntry::make('engraving_total')->label('Engraving')->money('BDT'),
                    TextEntry::make('discount')->money('BDT')->helperText(fn (Order $record) => $record->coupon_code),
                    TextEntry::make('shipping_charge')->label('Shipping')->money('BDT'),
                    TextEntry::make('total')->money('BDT')->weight('bold')->size('lg'),
                ]),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Status')->schema([
                    Grid::make(2)->schema([
                        TextEntry::make('status')->badge(),
                        TextEntry::make('payment_status')->badge(),
                    ]),
                    TextEntry::make('paymentMethodModel.name')->label('Payment method')->placeholder(fn (Order $record) => $record->payment_method),
                    TextEntry::make('created_at')->label('Placed')->dateTime('d M Y, h:i A'),
                    TextEntry::make('courier_name')->placeholder('Not shipped')
                        ->helperText(fn (Order $record) => $record->tracking_number ? 'Tracking: '.$record->tracking_number : null),
                ]),
                Section::make('Customer')->schema([
                    TextEntry::make('customer_name')->label('Name')
                        ->helperText(fn (Order $record) => $record->user_id ? 'Registered customer' : 'Guest checkout'),
                    TextEntry::make('phone')->copyable()->url(fn (Order $record) => 'tel:'.$record->phone),
                    TextEntry::make('email')->placeholder('—')->copyable(),
                    TextEntry::make('address')->label('Delivery address')->state(fn (Order $record) => $record->fullAddress()),
                    TextEntry::make('shipping_zone_name')->label('Delivery zone'),
                    TextEntry::make('customer_note')->placeholder('—'),
                    TextEntry::make('admin_note')->placeholder('—'),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->withCount('items'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order_number')->label('Order')->searchable()->weight('bold')->copyable(),
                TextColumn::make('customer_name')->label('Customer')->searchable()->description(fn (Order $record) => $record->phone),
                TextColumn::make('phone')->searchable()->hidden(),
                TextColumn::make('items_count')->label('Items'),
                TextColumn::make('total')->money('BDT')->sortable(),
                TextColumn::make('payment_method')->badge()->color('gray'),
                TextColumn::make('payment_status')->badge(),
                TextColumn::make('status')->badge(),
                TextColumn::make('shipping_zone_name')->label('Zone')->toggleable(),
                TextColumn::make('created_at')->label('Date')->dateTime('d M Y, h:i A')->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(OrderStatus::class)->multiple(),
                SelectFilter::make('payment_status')->options(PaymentStatus::class),
                SelectFilter::make('payment_method')->relationship('paymentMethodModel', 'name'),
                Filter::make('created_at')->schema([DatePicker::make('from'), DatePicker::make('until')])
                    ->query(fn (Builder $query, array $data) => $query
                        ->when($data['from'], fn ($q, $d) => $q->whereDate('created_at', '>=', $d))
                        ->when($data['until'], fn ($q, $d) => $q->whereDate('created_at', '<=', $d))),
            ])
            ->recordActions([
                ViewAction::make(),
                ActionGroup::make([
                    self::changeStatusAction(),
                    self::paymentStatusAction(),
                    EditAction::make(),
                    self::invoiceAction(),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('bulk_status')->label('Change status')->icon(Heroicon::OutlinedArrowPath)
                        ->authorize(fn () => static::can('update'))
                        ->schema([Select::make('status')->options(OrderStatus::class)->required()])
                        ->action(function (Collection $records, array $data) {
                            $service = app(OrderService::class);
                            $records->each(fn (Order $order) => $service->changeStatus($order, OrderStatus::from($data['status']), 'Bulk update'));
                            Notification::make()->success()->title('Orders updated')->send();
                        })->deselectRecordsAfterCompletion(),
                ]),
            ]);
    }

    public static function changeStatusAction(): Action
    {
        return Action::make('change_status')
            ->label('Change status')
            ->icon(Heroicon::OutlinedArrowPath)
            ->color('primary')
            ->authorize(fn () => static::can('update'))
            ->fillForm(fn (Order $record) => ['status' => $record->status, 'courier_name' => $record->courier_name, 'tracking_number' => $record->tracking_number])
            ->schema([
                Select::make('status')->options(OrderStatus::class)->required()->native(false)
                    ->helperText('Cancelled / Returned automatically restocks items.'),
                Grid::make(2)->schema([
                    TextInput::make('courier_name'),
                    TextInput::make('tracking_number'),
                ]),
                TextInput::make('note')->maxLength(255)->helperText('Visible in the order timeline.'),
            ])
            ->action(function (Order $record, array $data) {
                $record->update(['courier_name' => $data['courier_name'], 'tracking_number' => $data['tracking_number']]);
                app(OrderService::class)->changeStatus($record, OrderStatus::from($data['status'] instanceof OrderStatus ? $data['status']->value : $data['status']), $data['note'] ?? null);
                Notification::make()->success()->title('Order status updated')->send();
            });
    }

    public static function paymentStatusAction(): Action
    {
        return Action::make('payment_status')
            ->label('Payment status')
            ->icon(Heroicon::OutlinedBanknotes)
            ->color('success')
            ->authorize(fn () => static::can('update'))
            ->fillForm(fn (Order $record) => ['payment_status' => $record->payment_status])
            ->schema([
                Select::make('payment_status')->options(PaymentStatus::class)->required()->native(false),
            ])
            ->action(function (Order $record, array $data) {
                $record->update(['payment_status' => $data['payment_status']]);
                Notification::make()->success()->title('Payment status updated')->send();
            });
    }

    public static function invoiceAction(): Action
    {
        return Action::make('invoice')->label('Print invoice')->icon(Heroicon::OutlinedPrinter)->color('gray')
            ->url(fn (Order $record) => route('orders.invoice', $record), shouldOpenInNewTab: true);
    }

    public static function getRelations(): array
    {
        return [
            PaymentsRelationManager::class,
            EngravingRequestsRelationManager::class,
            StatusHistoriesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListOrders::route('/'),
            'view' => ViewOrder::route('/{record}'),
            'edit' => EditOrder::route('/{record}/edit'),
        ];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Order::where('status', OrderStatus::Pending)->count();

        return $pending ? (string) $pending : null;
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Pending orders';
    }
}
