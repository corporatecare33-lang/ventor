<?php

namespace App\Filament\Resources\Roles;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Roles\Pages\ListRoles;
use App\Support\Permissions;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\Role;
use UnitEnum;

class RoleResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Role::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedKey;

    protected static string|UnitEnum|null $navigationGroup = 'Access';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Roles & permissions';

    /** Core roles the application depends on. */
    private const PROTECTED_ROLES = ['super_admin', 'customer'];

    protected static function permissionArea(): string
    {
        return 'users';
    }

    public static function canEdit(Model $record): bool
    {
        return $record->name !== 'super_admin' && parent::canEdit($record);
    }

    public static function canDelete(Model $record): bool
    {
        return ! in_array($record->name, [...self::PROTECTED_ROLES, 'admin', 'manager', 'support'], true) && parent::canDelete($record);
    }

    public static function form(Schema $schema): Schema
    {
        $options = collect(Permissions::all())->mapWithKeys(fn (string $p) => [$p => ucfirst($p)])->all();

        return $schema->columns(1)->components([
            TextInput::make('name')->required()->alphaDash()->maxLength(50)->unique(ignoreRecord: true)
                ->disabled(fn (?Role $record) => $record && in_array($record->name, self::PROTECTED_ROLES, true)),
            CheckboxList::make('permissions')->relationship('permissions', 'name')
                ->options($options)->columns(2)->bulkToggleable()
                ->helperText('"View" = read-only. "Manage" = create, edit and delete.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($q) => $q->withCount(['permissions', 'users']))
            ->columns([
                TextColumn::make('name')->badge(),
                TextColumn::make('permissions_count')->label('Permissions')
                    ->formatStateUsing(fn (Role $r, $state) => $r->name === 'super_admin' ? 'All (bypass)' : $state),
                TextColumn::make('users_count')->label('Users'),
            ])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ListRoles::route('/')];
    }
}
