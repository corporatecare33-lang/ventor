<?php

namespace App\Filament\Pages;

use App\Filament\Support\SettingsPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Support\Icons\Heroicon;

class SeoSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMagnifyingGlass;

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'SEO settings';

    protected static ?string $navigationLabel = 'SEO';

    protected function fields(): array
    {
        return [
            Section::make('Defaults')->columns(2)->schema([
                TextInput::make('seo_title_suffix')->default(' | LeatherX')->helperText('Appended to every page title.'),
                TextInput::make('seo_home_title')->maxLength(70),
                Textarea::make('seo_default_description')->maxLength(300)->rows(3)->columnSpanFull(),
                TextInput::make('seo_keywords')->columnSpanFull(),
                FileUpload::make('seo_og_image')->label('Default social share image (1200×630)')->image()->disk('public')->directory('settings')->maxSize(2048),
            ]),
            Section::make('Indexing & tracking')->columns(2)->schema([
                Toggle::make('seo_allow_indexing')->label('Allow search engines to index the site')->default(true)
                    ->helperText('Turn off on staging sites.'),
                TextInput::make('google_site_verification'),
                TextInput::make('google_analytics_id')->placeholder('G-XXXXXXXXXX'),
                TextInput::make('facebook_pixel_id')->placeholder('Meta Pixel ID'),
                Textarea::make('robots_extra')->label('Extra robots.txt rules')->rows(3)->columnSpanFull(),
            ]),
        ];
    }
}
