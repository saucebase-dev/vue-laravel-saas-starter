<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Resources\Pages\CreateRecord;
use Modules\Blog\Filament\Resources\Blog\TagResource;
use Modules\Blog\Filament\Traits\BreadcrumbsUnderPosts;

class CreateTag extends CreateRecord
{
    use BreadcrumbsUnderPosts;

    protected static string $resource = TagResource::class;
}
