<?php

namespace App\Filament\Resources\HomeSections;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\HomeSections\Pages\ListHomeSections;
use App\Models\Category;
use App\Models\HomeSection;
use App\Support\CollectionSummary;
use App\Support\Comparison;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Repeater\TableColumn;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use UnitEnum;

class HomeSectionResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = HomeSection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedHome;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Homepage sections';

    protected static function permissionArea(): string
    {
        return 'marketing';
    }

    public static function form(Schema $schema): Schema
    {
        $isComparison = fn ($get) => $get('key') === 'comparison';

        return $schema->columns(2)->components([
            Select::make('key')->label('Section')->options(HomeSection::KEYS)->required()->disabledOn('edit')
                ->unique(ignoreRecord: true)->live()
                ->afterStateUpdated(fn (?string $state, $set) => $state === 'comparison' ? $set('content', Comparison::defaults()) : null),
            Toggle::make('is_active')->label('Visible on homepage')->default(true)->inline(false),
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('subtitle')->rows(2)->maxLength(500)->columnSpanFull(),
            self::comparisonSection()->visible($isComparison),
            Section::make('Content')->columnSpanFull()->hidden($isComparison)->description('Cards / steps / badges shown in this section.')->schema([
                Repeater::make('content.items')->hiddenLabel()->columns(3)->collapsible()->reorderable()
                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(120),
                        Select::make('icon')->options(self::icons())->native(false),
                        TextInput::make('meta')->label('Small label / value')->maxLength(60),
                        Textarea::make('text')->rows(2)->maxLength(400)->columnSpanFull(),
                    ]),
                TagsInput::make('content.points')->label('Short points')->helperText('Shown as a row of check marks, e.g. “No app needed”.')->columnSpanFull(),
                TextInput::make('content.button_text')->maxLength(40),
                TextInput::make('content.button_link')->maxLength(255),
            ]),
            SpatieMediaLibraryFileUpload::make('image')->collection('image')->image()->maxSize(5120)->hidden($isComparison)
                ->columnSpanFull()->helperText('Section photograph (landscape, at least 1600px wide). Converted to WebP automatically.')
                ->customProperties(fn ($get) => ['alt' => $get('title')]),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
    }

    /**
     * The comparison table (see App\Support\Comparison). Each row is edited like the table it
     * produces; a catalogue row's empty cells show, as placeholders, the value read from the
     * chosen category's live products.
     */
    private static function comparisonSection(): Section
    {
        $categories = fn () => once(fn () => Category::orderBy('sort_order')->orderBy('name')->pluck('name', 'id'));
        $categoryName = fn ($id) => $categories()[$id] ?? 'Category name';
        $columns = ['first' => 'First column', 'second' => 'Second column'];

        return Section::make('Comparison table')->columnSpanFull()->columns(2)
            ->description('Rows set to a catalogue value read it from each category’s live products — type in a cell to replace it. Type “Yes” for a check mark and “—” for a dash. Rows where both columns match move to the “included in both” line.')
            ->schema([
                TextInput::make('content.eyebrow')->label('Small label above the title')->placeholder(Comparison::DEFAULT_EYEBROW)->maxLength(40),
                TextInput::make('content.included_label')->label('“Included in both” heading')->placeholder(Comparison::DEFAULT_INCLUDED_LABEL)->maxLength(60),
                Select::make('content.first_category_id')->label('First column category')->options($categories)->required()->live()
                    ->different('content.second_category_id'),
                Select::make('content.second_category_id')->label('Second column category')->options($categories)->required()->live(),
                TextInput::make('content.first_label')->label('First column heading')->maxLength(40)->live(onBlur: true)
                    ->placeholder(fn ($get) => $categoryName($get('content.first_category_id'))),
                TextInput::make('content.second_label')->label('Second column heading')->maxLength(40)->live(onBlur: true)
                    ->placeholder(fn ($get) => $categoryName($get('content.second_category_id'))),
                TextInput::make('content.first_button')->label('First button')->maxLength(40)
                    ->placeholder(fn ($get) => 'Shop '.($get('content.first_label') ?: $categoryName($get('content.first_category_id')))),
                TextInput::make('content.second_button')->label('Second button')->maxLength(40)
                    ->placeholder(fn ($get) => 'Shop '.($get('content.second_label') ?: $categoryName($get('content.second_category_id')))),
                Repeater::make('content.rows')->label('Rows')->columnSpanFull()->reorderable()->addActionLabel('Add row')
                    ->table([
                        TableColumn::make('Value from')->width('12rem'),
                        TableColumn::make('Row label'),
                        TableColumn::make('First column'),
                        TableColumn::make('Second column'),
                        TableColumn::make('Highlight')->width('10rem'),
                    ])
                    ->schema([
                        Select::make('metric')->options(['custom' => 'Typed values', ...CollectionSummary::METRICS])
                            ->default('custom')->selectablePlaceholder(false)->required()->live(),
                        TextInput::make('label')->maxLength(60)
                            ->placeholder(fn ($get) => CollectionSummary::METRICS[$get('metric')] ?? null)
                            ->required(fn ($get) => ! isset(CollectionSummary::METRICS[$get('metric')])),
                        TextInput::make('first')->maxLength(80)
                            ->placeholder(fn ($get) => Comparison::preview($get('metric'), $get('../../first_category_id')) ?? 'Yes, — or text'),
                        TextInput::make('second')->maxLength(80)
                            ->placeholder(fn ($get) => Comparison::preview($get('metric'), $get('../../second_category_id')) ?? 'Yes, — or text'),
                        Select::make('highlight')->options($columns)->placeholder('None'),
                    ]),
                TagsInput::make('content.included')->label('Also included in both')->columnSpanFull()
                    ->helperText('Features both collections share that no row covers, e.g. “Magnetic lock”. Listed after the matching rows.'),
            ]);
    }

    /** Opens an unsaved comparison section with the defaults the homepage is already using. */
    private static function withComparisonDefaults(array $data): array
    {
        if (($data['key'] ?? null) === 'comparison') {
            $data['content'] = [...Comparison::defaults(), ...array_filter($data['content'] ?? [], fn ($value) => $value !== null)];
        }

        return $data;
    }

    /** @return array<string, string> Icons available in resources/views/components/icon.blade.php */
    public static function icons(): array
    {
        return [
            'shield' => 'Shield', 'signal' => 'NFC / signal', 'truck' => 'Truck', 'badge' => 'Badge / warranty',
            'sparkles' => 'Sparkles', 'pen' => 'Pen', 'gift' => 'Gift', 'cash' => 'Cash', 'phone' => 'Phone',
            'card' => 'Card', 'leather' => 'Leather', 'check' => 'Check', 'star' => 'Star', 'building' => 'Building',
        ];
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->paginated(false)
            ->columns([
                TextColumn::make('key')->formatStateUsing(fn (string $state) => HomeSection::KEYS[$state] ?? $state)->badge()->color('gray'),
                TextColumn::make('title')->searchable()->limit(50),
                IconColumn::make('is_active')->label('Visible')->boolean(),
            ])
            ->recordActions([
                EditAction::make()->slideOver()
                    ->modalWidth(fn (HomeSection $record) => $record->key === 'comparison' ? Width::SevenExtraLarge : null)
                    ->mutateRecordDataUsing(fn (array $data) => self::withComparisonDefaults($data)),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListHomeSections::route('/')];
    }
}
