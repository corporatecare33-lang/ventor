<?php

namespace App\Filament\Resources\Pages;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Pages\Pages\CreatePage;
use App\Filament\Resources\Pages\Pages\EditPage;
use App\Filament\Resources\Pages\Pages\ListPages;
use App\Models\Page;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PageResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Page::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentText;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'CMS pages';

    protected static function permissionArea(): string
    {
        return 'content';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make()->columnSpan(['lg' => 2])->schema([
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true)->prefix(url('/page').'/'),
                RichEditor::make('content')->columnSpanFull(),
            ]),
            Section::make('Publish & SEO')->columnSpan(['lg' => 1])->schema([
                Toggle::make('is_active')->default(true),
                TextInput::make('seo_title')->maxLength(70),
                Textarea::make('seo_description')->maxLength(300)->rows(3),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->searchable()->weight('medium'),
                TextColumn::make('slug')->prefix('/page/')->color('gray'),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('updated_at')->since(),
            ])
            ->recordActions([
                Action::make('view')->icon(Heroicon::OutlinedEye)->color('gray')->url(fn (Page $r) => route('pages.show', $r), shouldOpenInNewTab: true),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPages::route('/'),
            'create' => CreatePage::route('/create'),
            'edit' => EditPage::route('/{record}/edit'),
        ];
    }
}
