<?php

namespace Modules\Blog\Filament\Resources\Blog;

use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Blog\Filament\Resources\Blog\Pages\CreatePost;
use Modules\Blog\Filament\Resources\Blog\Pages\EditPost;
use Modules\Blog\Filament\Resources\Blog\Pages\ListPosts;
use Modules\Blog\Filament\Resources\Blog\Schemas\PostForm;
use Modules\Blog\Filament\Resources\Blog\Tables\PostsTable;
use Modules\Blog\Models\Post;

class PostResource extends Resource
{
    protected static ?string $model = Post::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationLabel = 'Blog';

    protected static ?int $navigationSort = 1;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage blog') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return PostForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return PostsTable::configure($table);
    }

    /** Opens the post's public page once it is live, the preview until then. */
    public static function viewPostAction(): Action
    {
        return Action::make('view_post')
            ->label(fn (Post $record): string => $record->isPubliclyVisible() ? __('View Post') : __('Preview'))
            ->icon('heroicon-o-arrow-top-right-on-square')
            ->color('gray')
            ->url(fn (Post $record): string => $record->viewUrl())
            ->openUrlInNewTab();
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
