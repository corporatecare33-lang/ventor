<?php

namespace App\Filament\Resources\Products;

use App\Enums\ProductStatus;
use App\Enums\StockMovementType;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Products\Pages\CreateProduct;
use App\Filament\Resources\Products\Pages\EditProduct;
use App\Filament\Resources\Products\Pages\ListProducts;
use App\Filament\Resources\Products\RelationManagers\ReviewsRelationManager;
use App\Filament\Resources\Products\RelationManagers\StockMovementsRelationManager;
use App\Filament\Resources\Products\RelationManagers\VariantsRelationManager;
use App\Models\Product;
use App\Services\InventoryService;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\ReplicateAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Grid;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;
use UnitEnum;

class ProductResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShoppingBag;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    protected static function permissionArea(): string
    {
        return 'catalog';
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'sku'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Group::make()->columnSpan(['lg' => 2])->schema([
                Section::make('Product')->schema([
                    TextInput::make('name')->required()->maxLength(255)->live(onBlur: true)
                        ->afterStateUpdated(function (Get $get, Set $set, ?string $state, string $operation) {
                            if ($operation === 'create' || blank($get('slug'))) {
                                $set('slug', Str::slug((string) $state));
                            }
                        }),
                    Grid::make(2)->schema([
                        TextInput::make('slug')->required()->maxLength(255)->unique(ignoreRecord: true)->alphaDash(),
                        TextInput::make('sku')->label('SKU')->required()->maxLength(64)->unique(ignoreRecord: true),
                    ]),
                    Textarea::make('short_description')->rows(2)->maxLength(500),
                    RichEditor::make('description')->columnSpanFull()
                        ->toolbarButtons([['bold', 'italic', 'underline', 'link'], ['h2', 'h3'], ['bulletList', 'orderedList', 'blockquote'], ['undo', 'redo']]),
                ]),
                Section::make('Images')->description('First image is the main product image. Upload WebP/JPG, ideally 1200×1200.')->schema([
                    SpatieMediaLibraryFileUpload::make('images')->collection('images')->multiple()->reorderable()
                        ->image()->maxSize(4096)->maxFiles(10)->panelLayout('grid')->hiddenLabel(),
                ]),
                Section::make('Pricing & Stock')->columns(3)->schema([
                    TextInput::make('regular_price')->numeric()->required()->prefix('৳')->minValue(0),
                    TextInput::make('sale_price')->numeric()->prefix('৳')->minValue(0)->lt('regular_price')
                        ->helperText('Leave empty for no discount.'),
                    TextInput::make('low_stock_threshold')->numeric()->default(5)->minValue(0),
                    TextInput::make('stock_quantity')->numeric()->default(0)->minValue(0)
                        ->disabledOn('edit')
                        ->helperText(fn (string $operation) => $operation === 'edit'
                            ? 'Use "Adjust stock" (or edit variant stock) so every change is logged.'
                            : 'Opening stock. With variants, product stock = sum of variant stock.'),
                ]),
                Section::make('Specifications')->columns(2)->schema([
                    TextInput::make('leather_type')->placeholder('Full-grain cowhide'),
                    TextInput::make('material')->placeholder('Leather + aluminium card cage'),
                    TextInput::make('card_capacity')->numeric()->minValue(0)->suffix('cards'),
                    TextInput::make('cash_capacity')->numeric()->minValue(0)->suffix('notes'),
                    TextInput::make('warranty_months')->numeric()->minValue(0)->default(12)->suffix('months'),
                    Grid::make(3)->columnSpanFull()->schema([
                        Toggle::make('rfid_protection')->label('RFID protection'),
                        Toggle::make('nfc_enabled')->label('NFC enabled (LeatherX Tap)'),
                        Toggle::make('engraving_available')->label('Engraving available')->live(),
                    ]),
                    TextInput::make('engraving_price')->numeric()->prefix('৳')->default(0)->minValue(0)
                        ->visible(fn (Get $get) => (bool) $get('engraving_available')),
                    KeyValue::make('specifications')->label('Extra specifications')->keyLabel('Spec')->valueLabel('Value')
                        ->columnSpanFull()->reorderable(),
                ]),
                Section::make('SEO')->collapsed()->columns(2)->schema([
                    TextInput::make('seo_title')->maxLength(70)->helperText('Defaults to product name.'),
                    TextInput::make('og_title')->label('Open Graph title')->maxLength(95),
                    Textarea::make('seo_description')->maxLength(300)->rows(2),
                    Textarea::make('og_description')->label('Open Graph description')->maxLength(300)->rows(2),
                ]),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Status')->schema([
                    Select::make('status')->options(ProductStatus::class)->default(ProductStatus::Draft)->required()->native(false),
                    Toggle::make('is_featured')->label('Featured on homepage'),
                    TextInput::make('sort_order')->numeric()->default(0),
                ]),
                Section::make('Category')->schema([
                    Select::make('category_id')->relationship('category', 'name')->searchable()->preload()
                        ->createOptionForm([
                            TextInput::make('name')->required(),
                            TextInput::make('slug')->required()->unique('categories', 'slug'),
                        ]),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['category:id,name', 'media'])->withCount('variants'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                SpatieMediaLibraryImageColumn::make('images')->collection('images')->conversion('thumb')->limit(1)->square()->label(''),
                TextColumn::make('name')->searchable()->sortable()->weight('medium')
                    ->description(fn (Product $record) => $record->sku),
                TextColumn::make('category.name')->badge()->color('gray')->toggleable(),
                TextColumn::make('regular_price')->money('BDT')->sortable()
                    ->description(fn (Product $record) => $record->hasDiscount() ? 'Sale '.money($record->sale_price) : null),
                TextColumn::make('stock_quantity')->label('Stock')->sortable()->badge()
                    ->color(fn (Product $record) => $record->stock_quantity <= 0 ? 'danger' : ($record->isLowStock() ? 'warning' : 'success')),
                TextColumn::make('variants_count')->label('Variants')->toggleable(),
                TextColumn::make('status')->badge()->sortable(),
                IconColumn::make('nfc_enabled')->label('NFC')->boolean()->toggleable(),
                IconColumn::make('is_featured')->label('Featured')->boolean()->toggleable(),
                TextColumn::make('sold_count')->label('Sold')->sortable()->toggleable(),
                TextColumn::make('rating_avg')->label('Rating')->sortable()->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')->since()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')->options(ProductStatus::class),
                SelectFilter::make('category')->relationship('category', 'name')->preload(),
                TernaryFilter::make('nfc_enabled')->label('NFC'),
                TernaryFilter::make('is_featured')->label('Featured'),
                Filter::make('low_stock')->label('Low stock')
                    ->query(fn (Builder $query) => $query->whereColumn('stock_quantity', '<=', 'low_stock_threshold')),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                ActionGroup::make([
                    self::adjustStockAction(),
                    Action::make('view_on_site')->label('View on site')->icon(Heroicon::OutlinedArrowTopRightOnSquare)
                        ->url(fn (Product $record) => route('products.show', $record), shouldOpenInNewTab: true),
                    ReplicateAction::make()->excludeAttributes(['sold_count', 'rating_avg', 'reviews_count', 'variants_count'])
                        ->mutateRecordDataUsing(function (array $data) {
                            $data['name'] .= ' (Copy)';
                            $data['slug'] = Str::slug($data['name']).'-'.Str::lower(Str::random(4));
                            $data['sku'] .= '-COPY'.Str::upper(Str::random(3));
                            $data['status'] = ProductStatus::Draft;
                            $data['stock_quantity'] = 0;

                            return $data;
                        })
                        ->schema([
                            TextInput::make('name')->required(),
                            TextInput::make('slug')->required()->unique('products', 'slug'),
                            TextInput::make('sku')->required()->unique('products', 'sku'),
                        ]),
                ]),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    BulkAction::make('activate')->label('Mark active')->icon(Heroicon::OutlinedCheckCircle)
                        ->action(fn (Collection $records) => $records->each->update(['status' => ProductStatus::Active]))
                        ->deselectRecordsAfterCompletion(),
                    BulkAction::make('draft')->label('Move to draft')->icon(Heroicon::OutlinedPencilSquare)
                        ->action(fn (Collection $records) => $records->each->update(['status' => ProductStatus::Draft]))
                        ->deselectRecordsAfterCompletion(),
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function adjustStockAction(): Action
    {
        return Action::make('adjust_stock')
            ->label('Adjust stock')
            ->icon(Heroicon::OutlinedArrowsUpDown)
            ->authorize(fn () => static::can('update'))
            ->schema(fn (Product $record) => [
                Select::make('product_variant_id')->label('Variant')
                    ->options($record->variants()->with('color', 'material', 'size')->get()->mapWithKeys(fn ($v) => [$v->id => $v->name().' — stock '.$v->stock_quantity]))
                    ->visible($record->variants()->exists())
                    ->required($record->variants()->exists()),
                Select::make('type')->options([
                    StockMovementType::Restock->value => 'Restock (add)',
                    StockMovementType::Adjustment->value => 'Adjustment (+/-)',
                    StockMovementType::Return->value => 'Customer return (add)',
                ])->default(StockMovementType::Restock->value)->required(),
                TextInput::make('quantity')->numeric()->required()->integer()->notIn([0])
                    ->helperText('Use a negative number to remove stock (adjustment only).'),
                TextInput::make('note')->maxLength(255),
            ])
            ->action(function (Product $record, array $data) {
                $type = StockMovementType::from($data['type']);
                $quantity = (int) $data['quantity'];
                if ($type !== StockMovementType::Adjustment) {
                    $quantity = abs($quantity);
                }
                $variant = isset($data['product_variant_id']) ? $record->variants()->find($data['product_variant_id']) : null;

                try {
                    $movement = app(InventoryService::class)->adjust($record, $variant, $quantity, $type, $data['note'] ?? null);
                    Notification::make()->success()->title("Stock updated. New balance: {$movement->balance_after}")->send();
                } catch (\InvalidArgumentException $e) {
                    Notification::make()->danger()->title($e->getMessage())->send();
                }
            });
    }

    public static function getRelations(): array
    {
        return [
            VariantsRelationManager::class,
            StockMovementsRelationManager::class,
            ReviewsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListProducts::route('/'),
            'create' => CreateProduct::route('/create'),
            'edit' => EditProduct::route('/{record}/edit'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([SoftDeletingScope::class]);
    }

    public static function getNavigationBadge(): ?string
    {
        $count = Product::where('status', '!=', ProductStatus::Draft)->whereColumn('stock_quantity', '<=', 'low_stock_threshold')->count();

        return $count ? (string) $count : null;
    }

    public static function getNavigationBadgeColor(): ?string
    {
        return 'warning';
    }

    public static function getNavigationBadgeTooltip(): ?string
    {
        return 'Low stock products';
    }
}
