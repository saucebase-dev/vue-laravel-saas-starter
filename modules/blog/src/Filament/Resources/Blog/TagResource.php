<?php

namespace Modules\Blog\Filament\Resources\Blog;

use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Tables\Table;
use Modules\Blog\Filament\Resources\Blog\Pages\CreateTag;
use Modules\Blog\Filament\Resources\Blog\Pages\EditTag;
use Modules\Blog\Filament\Resources\Blog\Pages\ListTags;
use Modules\Blog\Filament\Resources\Blog\Schemas\TagForm;
use Modules\Blog\Filament\Resources\Blog\Tables\TagsTable;
use Modules\Blog\Models\Tag;

class TagResource extends Resource
{
    protected static ?string $model = Tag::class;

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-hashtag';

    protected static ?int $navigationSort = 3;

    protected static bool $shouldRegisterNavigation = false;

    public static function canAccess(): bool
    {
        return auth()->user()?->can('manage blog') ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return TagForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TagsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTags::route('/'),
            'create' => CreateTag::route('/create'),
            'edit' => EditTag::route('/{record}/edit'),
        ];
    }
}
