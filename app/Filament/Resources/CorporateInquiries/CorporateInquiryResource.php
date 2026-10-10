<?php

namespace App\Filament\Resources\CorporateInquiries;

use App\Enums\InquiryStatus;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\CorporateInquiries\Pages\ListCorporateInquiries;
use App\Models\CorporateInquiry;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class CorporateInquiryResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = CorporateInquiry::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 4;

    protected static ?string $navigationLabel = 'Corporate / bulk orders';

    protected static function permissionArea(): string
    {
        return 'services';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('company_name')->required(),
            TextInput::make('contact_person')->required(),
            TextInput::make('designation'),
            TextInput::make('email')->email()->required(),
            TextInput::make('phone')->tel()->required(),
            TextInput::make('quantity')->numeric()->integer()->required(),
            TextInput::make('product_interest'),
            TextInput::make('budget'),
            DatePicker::make('deadline'),
            Toggle::make('branding_required')->inline(false),
            Textarea::make('message')->rows(3)->columnSpanFull(),
            Select::make('status')->options(InquiryStatus::class)->required(),
            Textarea::make('admin_note')->rows(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('company_name')->searchable()->weight('medium')->description(fn (CorporateInquiry $r) => $r->contact_person.' · '.$r->phone),
                TextColumn::make('quantity')->sortable(),
                TextColumn::make('product_interest')->limit(30)->placeholder('—'),
                TextColumn::make('deadline')->date('d M Y')->placeholder('—'),
                IconColumn::make('branding_required')->label('Branding')->boolean(),
                SelectColumn::make('status')->options(InquiryStatus::options())
                    ->disabled(fn () => ! static::can('update')),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(InquiryStatus::class)])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListCorporateInquiries::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $new = CorporateInquiry::where('status', InquiryStatus::New)->count();

        return $new ? (string) $new : null;
    }
}
