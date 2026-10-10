<?php

namespace App\Filament\Resources\HomeSections;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\HomeSections\Pages\ListHomeSections;
use App\Models\HomeSection;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
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
        return $schema->columns(2)->components([
            Select::make('key')->label('Section')->options(HomeSection::KEYS)->required()->disabledOn('edit')
                ->unique(ignoreRecord: true),
            Toggle::make('is_active')->label('Visible on homepage')->default(true)->inline(false),
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('subtitle')->rows(2)->maxLength(500)->columnSpanFull(),
            Section::make('Content')->columnSpanFull()->description('Cards / steps / badges shown in this section.')->schema([
                Repeater::make('content.items')->hiddenLabel()->columns(3)->collapsible()->reorderable()
                    ->itemLabel(fn (array $state) => $state['title'] ?? null)
                    ->schema([
                        TextInput::make('title')->required()->maxLength(120),
                        Select::make('icon')->options(self::icons())->native(false),
                        TextInput::make('meta')->label('Small label / value')->maxLength(60),
                        Textarea::make('text')->rows(2)->maxLength(400)->columnSpanFull(),
                    ]),
                TextInput::make('content.button_text')->maxLength(40),
                TextInput::make('content.button_link')->maxLength(255),
            ]),
            TextInput::make('sort_order')->numeric()->default(0),
        ]);
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
            ->recordActions([EditAction::make()->slideOver()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListHomeSections::route('/')];
    }
}
