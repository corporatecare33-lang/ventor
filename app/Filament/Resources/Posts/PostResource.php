<?php

namespace App\Filament\Resources\Posts;

use App\Filament\Concerns\ChecksPermission;
use App\Filament\Resources\Posts\Pages\CreatePost;
use App\Filament\Resources\Posts\Pages\EditPost;
use App\Filament\Resources\Posts\Pages\ListPosts;
use App\Models\Post;
use App\Models\User;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Group;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Support\Str;
use UnitEnum;

class PostResource extends Resource
{
    use ChecksPermission;

    protected static ?string $model = Post::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|UnitEnum|null $navigationGroup = 'Content';

    protected static ?int $navigationSort = 2;

    protected static ?string $navigationLabel = 'Blog / news';

    protected static ?string $recordTitleAttribute = 'title';

    protected static function permissionArea(): string
    {
        return 'content';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->columns(3)->components([
            Section::make()->columnSpan(['lg' => 2])->schema([
                TextInput::make('title')->required()->maxLength(255)->live(onBlur: true)
                    ->afterStateUpdated(fn (Set $set, ?string $state, string $operation) => $operation === 'create' ? $set('slug', Str::slug((string) $state)) : null),
                TextInput::make('slug')->required()->alphaDash()->unique(ignoreRecord: true),
                Textarea::make('excerpt')->rows(2)->maxLength(500),
                RichEditor::make('body')->required()->columnSpanFull(),
            ]),
            Group::make()->columnSpan(['lg' => 1])->schema([
                Section::make('Publish')->schema([
                    Toggle::make('is_published'),
                    DateTimePicker::make('published_at')->default(now()),
                    Select::make('category')->options(fn () => Post::query()->whereNotNull('category')->distinct()->pluck('category', 'category'))
                        ->searchable()->createOptionForm([TextInput::make('category')->required()])
                        ->createOptionUsing(fn (array $data) => $data['category']),
                    Select::make('author_id')->relationship('author', 'name', fn ($query) => $query->role(User::ADMIN_ROLES))
                        ->default(fn () => auth()->id()),
                    SpatieMediaLibraryFileUpload::make('cover')->collection('cover')->image()->maxSize(3072),
                ]),
                Section::make('SEO')->collapsed()->schema([
                    TextInput::make('seo_title')->maxLength(70),
                    Textarea::make('seo_description')->maxLength(300)->rows(3),
                ]),
            ]),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('published_at', 'desc')
            ->columns([
                TextColumn::make('title')->searchable()->limit(50)->weight('medium'),
                TextColumn::make('category')->badge()->color('gray')->placeholder('—'),
                IconColumn::make('is_published')->label('Published')->boolean(),
                TextColumn::make('published_at')->dateTime('d M Y')->sortable(),
            ])
            ->filters([TernaryFilter::make('is_published')])
            ->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListPosts::route('/'),
            'create' => CreatePost::route('/create'),
            'edit' => EditPost::route('/{record}/edit'),
        ];
    }
}
