<?php

namespace App\Filament\Resources\EngravingRequests;

use App\Enums\EngravingStatus;
use App\Enums\EngravingType;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\EngravingRequests\Pages\ListEngravingRequests;
use App\Filament\Resources\Orders\OrderResource;
use App\Models\EngravingRequest;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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

class EngravingRequestResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = EngravingRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Engraving requests';

    protected static function permissionArea(): string
    {
        return 'services';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('product_id')->relationship('product', 'name')->searchable()->preload(),
            TextInput::make('customer_name')->maxLength(255),
            TextInput::make('phone')->tel()->maxLength(20),
            TextInput::make('email')->email(),
            Select::make('type')->options(EngravingType::class)->required(),
            TextInput::make('text')->label('Engraving text')->maxLength(60),
            Select::make('font')->options(array_combine(EngravingRequest::FONTS, EngravingRequest::FONTS)),
            Select::make('position')->options(array_combine(EngravingRequest::POSITIONS, EngravingRequest::POSITIONS)),
            TextInput::make('quantity')->numeric()->integer()->minValue(1)->default(1),
            TextInput::make('fee')->numeric()->prefix('৳')->default(0),
            Textarea::make('logo_instruction')->rows(2)->columnSpanFull(),
            Select::make('status')->options(EngravingStatus::class)->default(EngravingStatus::Pending)->required(),
            Textarea::make('admin_note')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['order:id,order_number', 'product:id,name']))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('order.order_number')->label('Order')->placeholder('Pre-order inquiry')->searchable()
                    ->url(fn (EngravingRequest $r) => $r->order ? OrderResource::getUrl('view', ['record' => $r->order]) : null),
                TextColumn::make('product.name')->limit(25)->placeholder('—'),
                TextColumn::make('customer_name')->searchable()->description(fn (EngravingRequest $r) => $r->phone),
                TextColumn::make('type')->badge(),
                TextColumn::make('text')->label('Text / instruction')->wrap()->limit(40)
                    ->state(fn (EngravingRequest $r) => $r->text ?: $r->logo_instruction)
                    ->description(fn (EngravingRequest $r) => collect([$r->font, $r->position])->filter()->implode(' · ')),
                TextColumn::make('quantity')->label('Qty'),
                TextColumn::make('status')->badge(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')->options(EngravingStatus::class),
                SelectFilter::make('type')->options(EngravingType::class),
            ])
            ->recordActions([
                ActionGroup::make([
                    ...collect([EngravingStatus::Approved, EngravingStatus::Completed, EngravingStatus::Rejected])->map(fn (EngravingStatus $status) => Action::make('mark_'.$status->value)
                        ->label('Mark '.strtolower($status->getLabel()))
                        ->color($status->getColor())
                        ->authorize(fn () => static::can('update'))
                        ->visible(fn (EngravingRequest $r) => $r->status !== $status)
                        ->schema([Textarea::make('admin_note')->rows(2)])
                        ->fillForm(fn (EngravingRequest $r) => ['admin_note' => $r->admin_note])
                        ->action(fn (EngravingRequest $r, array $data) => $r->update(['status' => $status, 'admin_note' => $data['admin_note']])))
                        ->all(),
                ])->label('Status')->icon(Heroicon::OutlinedArrowPath)->button()->size('sm'),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListEngravingRequests::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = EngravingRequest::where('status', EngravingStatus::Pending)->count();

        return $pending ? (string) $pending : null;
    }
}
