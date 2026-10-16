<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Modules\Blog\Filament\Resources\Blog\TagResource;
use Modules\Blog\Filament\Traits\BreadcrumbsUnderPosts;

class ListTags extends ListRecords
{
    use BreadcrumbsUnderPosts;

    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
