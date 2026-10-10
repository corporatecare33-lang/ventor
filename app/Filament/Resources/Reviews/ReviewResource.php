<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Reviews\Pages\ListReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Collection;
use UnitEnum;

class ReviewResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 5;

    protected static function permissionArea(): string
    {
        return 'catalog';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(2)->components([
            Select::make('product_id')->relationship('product', 'name')->searchable()->preload()->required()
                ->hidden(fn ($livewire) => $livewire instanceof RelationManager),
            TextInput::make('name')->required()->maxLength(100),
            Select::make('rating')->options([5 => '★★★★★ 5', 4 => '★★★★ 4', 3 => '★★★ 3', 2 => '★★ 2', 1 => '★ 1'])->required()->default(5),
            TextInput::make('title')->maxLength(150),
            Textarea::make('body')->required()->rows(4)->columnSpanFull(),
            Textarea::make('admin_reply')->rows(2)->columnSpanFull()->helperText('Shown publicly under the review.'),
            Toggle::make('is_approved')->label('Approved (visible on site)'),
            Toggle::make('is_verified_purchase')->label('Verified purchase'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn ($query) => $query->with('product:id,name,slug'))
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('product.name')->searchable()->limit(30),
                TextColumn::make('name')->label('Customer')->searchable(),
                TextColumn::make('rating')->formatStateUsing(fn (int $state) => str_repeat('★', $state).str_repeat('☆', 5 - $state))->color('warning')->sortable(),
                TextColumn::make('body')->limit(60)->wrap()->description(fn (Review $record) => $record->title),
                IconColumn::make('is_verified_purchase')->label('Verified')->boolean(),
                IconColumn::make('is_approved')->label('Approved')->boolean(),
                TextColumn::make('created_at')->since()->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_approved')->label('Approved'),
                SelectFilter::make('rating')->options([5 => '5', 4 => '4', 3 => '3', 2 => '2', 1 => '1']),
            ])
            ->recordActions([
                Action::make('approve')->icon(Heroicon::OutlinedCheck)->color('success')
                    ->visible(fn (Review $record) => ! $record->is_approved)
                    ->action(fn (Review $record) => $record->update(['is_approved' => true])),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([BulkActionGroup::make([
                BulkAction::make('approve')->icon(Heroicon::OutlinedCheck)
                    ->action(fn (Collection $records) => $records->each->update(['is_approved' => true]))->deselectRecordsAfterCompletion(),
                BulkAction::make('unapprove')->label('Hide')->icon(Heroicon::OutlinedEyeSlash)
                    ->action(fn (Collection $records) => $records->each->update(['is_approved' => false]))->deselectRecordsAfterCompletion(),
                DeleteBulkAction::make(),
            ])]);
    }

    public static function getPages(): array
    {
        return ['index' => ListReviews::route('/')];
    }

    public static function getNavigationBadge(): ?string
    {
        $pending = Review::where('is_approved', false)->count();

        return $pending ? (string) $pending : null;
    }
}
