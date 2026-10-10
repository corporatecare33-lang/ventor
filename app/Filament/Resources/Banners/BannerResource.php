<?php

namespace App\Filament\Resources\Banners;

use App\Enums\BannerPosition;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Banners\Pages\ListBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class BannerResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|UnitEnum|null $navigationGroup = 'Marketing';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Banners & announcement';

    protected static function permissionArea(): string
    {
        return 'marketing';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('position')->options(BannerPosition::class)->required()->default(BannerPosition::Hero)->live(),
            TextInput::make('eyebrow')->label('Small label above title')->maxLength(80),
            TextInput::make('title')->required()->maxLength(255)->columnSpanFull(),
            Textarea::make('subtitle')->maxLength(500)->rows(2)->columnSpanFull(),
            TextInput::make('button_text')->maxLength(40),
            TextInput::make('button_link')->maxLength(255)->placeholder('/shop'),
            TextInput::make('secondary_button_text')->maxLength(40),
            TextInput::make('secondary_button_link')->maxLength(255),
            DateTimePicker::make('starts_at'),
            DateTimePicker::make('ends_at')->after('starts_at'),
            TextInput::make('sort_order')->numeric()->default(0),
            Toggle::make('is_active')->default(true)->inline(false),
            SpatieMediaLibraryFileUpload::make('image')->label('Desktop image (1920×900)')->collection('image')->image()->maxSize(4096),
            SpatieMediaLibraryFileUpload::make('mobile_image')->label('Mobile image (900×1200)')->collection('mobile_image')->image()->maxSize(3072),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($q) => $q->with('media'))
            ->defaultSort('sort_order')
            ->reorderable('sort_order')
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')->collection('image')->label(''),
                TextColumn::make('title')->searchable()->limit(50)->description(fn (Banner $r) => $r->subtitle ? str($r->subtitle)->limit(60) : null),
                TextColumn::make('position')->badge(),
                TextColumn::make('ends_at')->label('Ends')->dateTime('d M Y')->placeholder('—'),
                IconColumn::make('is_active')->boolean(),
            ])
            ->filters([SelectFilter::make('position')->options(BannerPosition::class)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListBanners::route('/')];
    }
}
