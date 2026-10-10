<?php

namespace App\Filament\Resources\Users;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Users\Pages\CreateUser;
use App\Filament\Resources\Users\Pages\EditUser;
use App\Filament\Resources\Users\Pages\ListUsers;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use UnitEnum;

/** Staff accounts that can sign in to the admin panel. */
class UserResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUserCircle;

    protected static string|UnitEnum|null $navigationGroup = 'Access';

    protected static ?int $navigationSort = 1;

    protected static ?string $navigationLabel = 'Admin users';

    protected static ?string $modelLabel = 'admin user';

    protected static function permissionArea(): string
    {
        return 'users';
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->role(User::ADMIN_ROLES);
    }

    public static function canDelete(Model $record): bool
    {
        return $record->id !== auth()->id() && parent::canDelete($record);
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            TextInput::make('name')->required()->maxLength(255),
            TextInput::make('email')->email()->required()->unique(ignoreRecord: true),
            TextInput::make('phone')->tel()->maxLength(20),
            TextInput::make('password')->password()->revealable()->rule('min:8')
                ->required(fn (string $operation) => $operation === 'create')
                ->dehydrated(fn (?string $state) => filled($state))
                ->helperText(fn (string $operation) => $operation === 'edit' ? 'Leave empty to keep the current password.' : null),
            Select::make('roles')->relationship('roles', 'name', fn ($query) => $query->whereIn('name', User::ADMIN_ROLES))
                ->multiple()->preload()->required()
                ->disabled(fn (?User $record) => $record?->is(auth()->user())),
            Toggle::make('is_active')->default(true)->inline(false)
                ->disabled(fn (?User $record) => $record?->is(auth()->user())),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('roles'))
            ->columns([
                TextColumn::make('name')->searchable()->description(fn (User $record) => $record->email),
                TextColumn::make('roles.name')->badge(),
                IconColumn::make('is_active')->boolean(),
                TextColumn::make('last_login_at')->since()->placeholder('Never'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListUsers::route('/'),
            'create' => CreateUser::route('/create'),
            'edit' => EditUser::route('/{record}/edit'),
        ];
    }
}
