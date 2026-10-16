<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Blog\Filament\Resources\Blog\CategoryResource;
use Modules\Blog\Filament\Resources\Blog\PostResource;
use Modules\Blog\Filament\Resources\Blog\TagResource;

class ListPosts extends ListRecords
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('categories')
                ->label(__('Manage Categories'))
                ->icon('heroicon-o-tag')
                ->url(CategoryResource::getUrl())
                ->color('gray'),
            Action::make('tags')
                ->label(__('Manage Tags'))
                ->icon('heroicon-o-hashtag')
                ->url(TagResource::getUrl())
                ->color('gray'),
            CreateAction::make(),
        ];
    }
}
