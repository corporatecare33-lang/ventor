<?php

namespace App\Filament\Resources\Categories;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Categories\Pages\CreateCategory;
use App\Filament\Resources\Categories\Pages\EditCategory;
use App\Filament\Resources\Categories\Pages\ListCategories;
use App\Models\Category;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class CategoryResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Category::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Categories / Series';

    protected static ?string $recordTitleAttribute = 'name';

    protected static function permissionArea(): string
    {
        return 'catalog';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make()->columnSpan(['lg' => 2])->columns(2)->schema([
                TextInput::make('name')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->unique(ignoreRecord: true)->alphaDash(),
                TextInput::make('tagline')->maxLength(255)->columnSpanFull()
                    ->placeholder('Full-grain leather | RFID protection | 2-year warranty'),
                Textarea::make('description')->rows(3)->columnSpanFull(),
                TextInput::make('seo_title')->maxLength(70),
                TextInput::make('seo_description')->maxLength(300),
            ]),
            Section::make()->columnSpan(['lg' => 1])->schema([
                Select::make('parent_id')->label('Parent category')->relationship('parent', 'name', fn ($query, ?Category $record) => $record ? $query->whereKeyNot($record->id) : $query)->searchable()->preload(),
                Toggle::make('is_active')->default(true),
                Toggle::make('is_featured')->label('Show as series on homepage'),
                TextInput::make('sort_order')->numeric()->default(0),
                SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->maxSize(2048),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with(['parent:id,name', 'media'])->withCount('products'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->label('')->square(),
                TextColumn::make('name')->searchable()->sortable()->weight('medium')->description(fn (Category $record) => $record->tagline),
                TextColumn::make('parent.name')->placeholder('—')->toggleable(),
                TextColumn::make('products_count')->label('Products')->sortable(),
                IconColumn::make('is_featured')->label('Homepage')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCategories::route('/'),
            'create' => CreateCategory::route('/create'),
            'edit' => EditCategory::route('/{record}/edit'),
        ];
    }
}
