<?php

namespace Modules\Blog\Filament\Resources\Blog\Pages;

use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;
use Modules\Blog\Filament\Resources\Blog\PostResource;

class EditPost extends EditRecord
{
    protected static string $resource = PostResource::class;

    protected function getHeaderActions(): array
    {
        return [
            PostResource::viewPostAction(),
            DeleteAction::make(),
        ];
    }
}
