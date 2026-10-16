<?php

namespace Modules\Blog\Filament\Traits;

use Modules\Blog\Filament\Resources\Blog\PostResource;

/**
 * Puts "Posts" ahead of a resource page's breadcrumbs, for resources reached from
 * the posts list rather than from the navigation.
 */
trait BreadcrumbsUnderPosts
{
    /**
     * @return array<string, string>
     */
    public function getResourceBreadcrumbs(): array
    {
        return [
            PostResource::getUrl() => PostResource::getBreadcrumb(),
            ...parent::getResourceBreadcrumbs(),
        ];
    }
}
