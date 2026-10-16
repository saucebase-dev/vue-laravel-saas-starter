<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Blog\Filament\Resources\Blog\TagResource;
use Modules\Blog\Filament\Traits\BreadcrumbsUnderPosts;

class EditTag extends EditRecord
{
    use BreadcrumbsUnderPosts;

    protected static string $resource = TagResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
