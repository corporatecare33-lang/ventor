<?php

namespace App\Filament\Support;

use App\Filament\Concerns\ChecksPagePermission;
use App\Models\Setting;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Actions;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\EmbeddedSchema;
use Filament\Schemas\Components\Form;
use Filament\Schemas\Schema;
use UnitEnum;

/**
 * Base class for key/value settings screens. Subclasses define the fields; every
 * field name is stored as a row in the `settings` table.
 */
abstract class SettingsPage extends Page
{
    use ChecksPagePermission;

    protected static string|UnitEnum|null $navigationGroup = 'Settings';

    /** @var array<string, mixed> */
    public ?array $data = [];

    protected static function permissionArea(): string
    {
        return 'settings';
    }

    /** @return array<int, Component> */
    abstract protected function fields(): array;

    public function mount(): void
    {
        $this->form->fill(Setting::allCached());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components($this->fields())->statePath('data');
    }

    public function content(Schema $schema): Schema
    {
        return $schema->components([
            Form::make([EmbeddedSchema::make('form')])
                ->id('form')
                ->livewireSubmitHandler('save')
                ->footer([
                    Actions::make([
                        Action::make('save')->label('Save settings')->submit('save')->keyBindings(['mod+s'])
                            ->visible(fn () => auth()->user()->can('manage settings')),
                    ]),
                ]),
        ]);
    }

    public function save(): void
    {
        abort_unless(auth()->user()->can('manage settings'), 403);

        Setting::setMany($this->form->getState());

        Notification::make()->success()->title('Settings saved')->send();
    }
}
