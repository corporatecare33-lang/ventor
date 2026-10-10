<?php

namespace App\Filament\Pages;

use App\Filament\Support\SettingsPage;
use BackedEnum;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Components\Text;
use Filament\Support\Icons\Heroicon;

class WebsiteSettings extends SettingsPage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCog6Tooth;

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Website settings';

    protected function fields(): array
    {
        return [
            Tabs::make()->tabs([
                Tab::make('General')->columns(2)->schema([
                    TextInput::make('site_name')->required()->default('LeatherX'),
                    TextInput::make('site_tagline')->maxLength(120),
                    FileUpload::make('site_logo')->image()->disk('public')->directory('settings')->maxSize(1024),
                    FileUpload::make('site_favicon')->image()->disk('public')->directory('settings')->maxSize(256),
                    Textarea::make('footer_about')->rows(3)->columnSpanFull(),
                    TextInput::make('copyright_text')->columnSpanFull(),
                ]),
                Tab::make('Contact')->columns(2)->schema([
                    TextInput::make('contact_phone')->tel(),
                    TextInput::make('contact_whatsapp')->tel(),
                    TextInput::make('contact_email')->email(),
                    TextInput::make('business_hours'),
                    Textarea::make('contact_address')->rows(2)->columnSpanFull(),
                    Textarea::make('google_map_embed')->label('Google Maps embed URL')->rows(2)->columnSpanFull()
                        ->helperText('Only the src URL from the Google Maps "Embed a map" code.'),
                ]),
                Tab::make('Social')->columns(2)->schema([
                    TextInput::make('social_facebook')->url(),
                    TextInput::make('social_instagram')->url(),
                    TextInput::make('social_youtube')->url(),
                    TextInput::make('social_tiktok')->url(),
                    TextInput::make('social_linkedin')->url(),
                ]),
                Tab::make('Store')->columns(2)->schema([
                    TextInput::make('announcement_text')->label('Top announcement bar')->maxLength(160)->columnSpanFull(),
                    TextInput::make('announcement_link')->maxLength(255),
                    Toggle::make('announcement_enabled')->inline(false),
                    TextInput::make('order_notification_email')->email()->helperText('New-order emails go here.'),
                    TextInput::make('trusted_customers')->label('Trust counter text')->placeholder('25,000+ happy customers'),
                    Textarea::make('checkout_note')->label('Checkout notice')->rows(2)->columnSpanFull(),
                    Section::make('Integrations (not connected yet)')->columnSpanFull()->schema([
                        Text::make('Courier APIs (Steadfast, Pathao, RedX), SMS gateways and online payment gateways are not connected. Add their API credentials to the .env file when available; see docs/DEPLOYMENT.md → "Third-party integrations".'),
                    ]),
                ]),
            ])->columnSpanFull(),
        ];
    }
}
