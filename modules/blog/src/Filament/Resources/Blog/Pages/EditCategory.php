<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Blog\Filament\Resources\Blog\CategoryResource;
use Modules\Blog\Filament\Traits\BreadcrumbsUnderPosts;

class EditCategory extends EditRecord
{
    use BreadcrumbsUnderPosts;

    protected static string $resource = CategoryResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
