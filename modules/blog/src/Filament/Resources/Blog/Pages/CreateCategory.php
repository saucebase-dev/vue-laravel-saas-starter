<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Blog\Filament\Resources\Blog\CategoryResource;
use Modules\Blog\Filament\Traits\BreadcrumbsUnderPosts;

class CreateCategory extends CreateRecord
{
    use BreadcrumbsUnderPosts;

    protected static string $resource = CategoryResource::class;
}
