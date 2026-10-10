<?php

namespace App\Filament\Resources\NfcProfiles;

use App\Enums\NfcProfileStatus;
use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\NfcProfiles\Pages\ListNfcProfiles;
use App\Models\NfcProfile;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use UnitEnum;

class NfcProfileResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = NfcProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'NFC profiles (Tap)';

    protected static ?string $modelLabel = 'NFC profile';

    protected static function permissionArea(): string
    {
        return 'services';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Section::make('Owner & link')->columns(2)->columnSpanFull()->schema([
                Select::make('user_id')->relationship('user', 'name')->searchable()->required(),
                Select::make('status')->options(NfcProfileStatus::class)->required()->default(NfcProfileStatus::Active),
                TextInput::make('slug')->label('Public URL slug')->required()->alphaDash()->unique(ignoreRecord: true)
                    ->prefix(url('/tap').'/')->helperText('Changing this breaks already-programmed NFC chips and printed QR codes.'),
                Select::make('product_id')->relationship('product', 'name', fn ($query) => $query->where('nfc_enabled', true))->searchable()->preload(),
            ]),
            TextInput::make('name')->required()->maxLength(100),
            TextInput::make('designation')->maxLength(100),
            TextInput::make('company')->maxLength(100),
            TextInput::make('phone')->tel()->maxLength(20),
            TextInput::make('email')->email(),
            TextInput::make('website')->url(),
            TextInput::make('facebook')->url(),
            TextInput::make('instagram')->url(),
            TextInput::make('linkedin')->url(),
            TextInput::make('whatsapp')->tel()->maxLength(20),
            Textarea::make('bio')->maxLength(500)->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('user:id,name,email'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('name')->searchable()->weight('medium')->description(fn (NfcProfile $record) => collect([$record->designation, $record->company])->filter()->implode(', ')),
                TextColumn::make('user.email')->label('Account')->searchable(),
                TextColumn::make('slug')->label('Public link')->copyable()->copyableState(fn (NfcProfile $record) => $record->publicUrl())->limit(30),
                TextColumn::make('views_count')->label('Taps / views')->sortable(),
                TextColumn::make('status')->badge(),
                TextColumn::make('updated_at')->since()->sortable(),
            ])
            ->filters([SelectFilter::make('status')->options(NfcProfileStatus::class)])
            ->recordActions([
                Action::make('qr')->label('QR')->icon(Heroicon::OutlinedQrCode)->color('gray')
                    ->visible(fn (NfcProfile $record) => $record->status === NfcProfileStatus::Active)
                    ->url(fn (NfcProfile $record) => route('nfc.qr', $record), shouldOpenInNewTab: true),
                Action::make('open')->icon(Heroicon::OutlinedArrowTopRightOnSquare)->color('gray')
                    ->url(fn (NfcProfile $record) => $record->publicUrl(), shouldOpenInNewTab: true),
                Action::make('toggle')
                    ->label(fn (NfcProfile $record) => $record->status === NfcProfileStatus::Active ? 'Suspend' : 'Activate')
                    ->color(fn (NfcProfile $record) => $record->status === NfcProfileStatus::Active ? 'danger' : 'success')
                    ->authorize(fn () => static::can('update'))
                    ->requiresConfirmation()
                    ->action(fn (NfcProfile $record) => $record->update(['status' => $record->status === NfcProfileStatus::Active ? NfcProfileStatus::Suspended : NfcProfileStatus::Active])),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    public static function getPages(): array
    {
        return ['index' => ListNfcProfiles::route('/')];
    }
}
