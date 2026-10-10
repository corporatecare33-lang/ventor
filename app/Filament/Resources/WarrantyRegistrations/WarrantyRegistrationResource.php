<?php

namespace App\Filament\Resources\WarrantyRegistrations;

use App\Enums\WarrantyStatus;
use App\Enums\WarrantyType;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Orders\OrderResource;
use App\Filament\Resources\WarrantyRegistrations\Pages\ListWarrantyRegistrations;
use App\Models\WarrantyRegistration;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class WarrantyRegistrationResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = WarrantyRegistration::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Warranty & claims';

    protected static ?string $modelLabel = 'warranty record';

    protected static function permissionArea(): string
    {
        return 'services';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('reference')->disabled()->dehydrated(false),
            Select::make('type')->options(WarrantyType::class)->required(),
            TextInput::make('customer_name')->required(),
            TextInput::make('phone')->required()->tel(),
            TextInput::make('email')->email(),
            TextInput::make('order_number')->required(),
            TextInput::make('product_name'),
            DatePicker::make('purchase_date'),
            DatePicker::make('expires_at')->label('Warranty valid until'),
            Select::make('status')->options(WarrantyStatus::class)->required(),
            Textarea::make('issue_details')->rows(3)->columnSpanFull(),
            Textarea::make('admin_note')->rows(2)->columnSpanFull()->helperText('Visible to the customer in their account.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('order:id,order_number'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('reference')->searchable()->weight('bold')->copyable(),
                TextColumn::make('type')->badge(),
                TextColumn::make('customer_name')->searchable()->description(fn (WarrantyRegistration $r) => $r->phone),
                TextColumn::make('order_number')->searchable()
                    ->url(fn (WarrantyRegistration $r) => $r->order ? OrderResource::getUrl('view', ['record' => $r->order]) : null),
                TextColumn::make('product_name')->limit(25),
                TextColumn::make('expires_at')->label('Valid until')->date('d M Y')->placeholder('—')
                    ->color(fn (WarrantyRegistration $r) => $r->expires_at && ! $r->isActive() ? 'danger' : 'success'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')->options(WarrantyType::class),
                SelectFilter::make('status')->options(WarrantyStatus::class),
            ])
            ->recordActions([
                Action::make('approve')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->authorize(fn () => static::can('update'))
                    ->visible(fn (WarrantyRegistration $r) => $r->status === WarrantyStatus::Pending)
                    ->schema([Textarea::make('admin_note')->rows(2)])
                    ->action(fn (WarrantyRegistration $r, array $data) => $r->update(['status' => WarrantyStatus::Approved, 'admin_note' => $data['admin_note']])),
                Action::make('reject')->icon(Heroicon::OutlinedXMark)->color('danger')
                    ->authorize(fn () => static::can('update'))
                    ->visible(fn (WarrantyRegistration $r) => $r->status === WarrantyStatus::Pending)
                    ->schema([Textarea::make('admin_note')->label('Reason')->required()->rows(2)])
                    ->action(fn (WarrantyRegistration $r, array $data) => $r->update(['status' => WarrantyStatus::Rejected, 'admin_note' => $data['admin_note']])),
                EditAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListWarrantyRegistrations::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = WarrantyRegistration::where('status', WarrantyStatus::Pending)->count();

        return $pending ? (string) $pending : null;
    }
}
