<?php

namespace App\Filament\Resources\ContactMessages;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\ContactMessages\Pages\ListContactMessages;
use App\Models\ContactMessage;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\ViewAction;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use UnitEnum;

class ContactMessageResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = ContactMessage::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Services';

    protected static ?int $navigationSort = 5;

    protected static ?string $navigationLabel = 'Contact messages';

    protected static function permissionArea(): string
    {
        return 'services';
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextEntry::make('name'),
            TextEntry::make('email')->copyable(),
            TextEntry::make('phone')->placeholder('—'),
            TextEntry::make('created_at')->dateTime('d M Y, h:i A'),
            TextEntry::make('subject')->columnSpanFull()->placeholder('—'),
            TextEntry::make('message')->columnSpanFull(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                IconColumn::make('is_read')->label('')->boolean()->trueIcon(Heroicon::OutlinedEnvelopeOpen)->falseIcon(Heroicon::OutlinedEnvelope)
                    ->falseColor('warning')->trueColor('gray'),
                TextColumn::make('name')->searchable()->description(fn (ContactMessage $r) => $r->email),
                TextColumn::make('subject')->limit(40)->placeholder('—'),
                TextColumn::make('message')->limit(60),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([TernaryFilter::make('is_read')->label('Read')])
            ->recordActions([
                ViewAction::make()->after(fn (ContactMessage $record) => $record->update(['is_read' => true])),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListContactMessages::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $unread = ContactMessage::where('is_read', false)->count();

        return $unread ? (string) $unread : null;
    }
}
