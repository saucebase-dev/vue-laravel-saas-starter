<?php

namespace Modules\Blog\Providers;

use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Saucebase\Core\Providers\ModuleServiceProvider;
use Saucebase\Core\Sitemap\SitemapRegistry;
use Spatie\Sitemap\Sitemap;

class BlogServiceProvider extends ModuleServiceProvider
{
    public function boot(): void
    {
        parent::boot();

        $this->app->make(SitemapRegistry::class)
            ->add(
                fn (Sitemap $sitemap) => $sitemap
                    ->add(route('blog.index'))
                    ->add(Post::published()->with(['category', 'media'])->get())
                    ->add(Category::withPublishedPosts()->get())
                    ->add(Tag::withPublishedPosts()->get())
            );
    }
}
