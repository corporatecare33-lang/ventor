<?php

namespace App\Filament\Resources\Faqs;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Models\Faq;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class FaqResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Faq::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedQuestionMarkCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'FAQ';

    protected static function permissionArea(): string
    {
        return 'content';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('question')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('answer')->required()->rows(4)->columnSpanFull(),
            TextInput::make('category')->default('General')->datalist(fn () => Faq::distinct()->pluck('category')->all()),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('show_on_home')->label('Show on homepage'),
            Toggle::make('is_active')->default(true),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                TextColumn::make('question')->searchable()->wrap(),
                TextColumn::make('category')->badge()->color('gray'),
                IconColumn::make('show_on_home')->label('Home')->boolean(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([SelectFilter::make('category')->options(fn () => Faq::distinct()->pluck('category', 'category'))])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListFaqs::route('/')];
    }
}
