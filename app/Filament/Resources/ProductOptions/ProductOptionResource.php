<?php

namespace App\Filament\Resources\ProductOptions;

use App\Enums\OptionType;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\ProductOptions\Pages\ListProductOptions;
use App\Models\ProductOption;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Validation\Rules\Unique;
use UnitEnum;

class ProductOptionResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = ProductOption::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSwatch;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 3;

    protected static ?string $navigationLabel = 'Colors / Materials / Sizes';

    protected static ?string $modelLabel = 'option';

    protected static function permissionArea(): string
    {
        return 'catalog';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('type')->options(OptionType::class)->required()->live()->default(OptionType::Color),
            TextInput::make('name')->required()->maxLength(100)
                ->unique(ignoreRecord: true, modifyRuleUsing: fn (Unique $rule, Get $get) => $rule->where('type', $get('type') instanceof OptionType ? $get('type')->value : $get('type'))),
            ColorPicker::make('value')->label('Swatch color')
                ->visible(fn (Get $get) => in_array($get('type'), [OptionType::Color, OptionType::Color->value], true)),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true),
        ])->columns(1);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                ColorColumn::make('value')->label(''),
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('type')->badge()->sortable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([SelectFilter::make('type')->options(OptionType::class)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListProductOptions::route('/')];
    }
}
