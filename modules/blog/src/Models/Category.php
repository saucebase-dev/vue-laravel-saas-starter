<?php

namespace Modules\Blog\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Blog\Contracts\Redirectable;
use Modules\Blog\Traits\HasRedirects;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 */
class Category extends Model implements Redirectable, Sitemapable
{
    use HasFactory, HasRedirects, Sluggable;

    protected $table = 'blog_categories';

    protected $fillable = ['name', 'slug'];

    /**
     * Slugs a category cannot take: its posts live at `/blog/{category}/{post}`,
     * and these words already begin the category, tag and preview URLs, which are
     * matched first.
     */
    public const RESERVED_SLUGS = ['category', 'tag', 'preview'];

    protected static function booted(): void
    {
        // The category's own page is remembered by `HasRedirects`; its posts'
        // URLs change too, without any post being saved.
        static::updated(function (Category $category): void {
            if ($category->wasChanged('slug')) {
                foreach ($category->posts as $post) {
                    $post->rememberPath(Post::pathFor($category->getOriginal('slug'), $post->slug));
                }
            }
        });

        // Its posts fall back to `/blog/{post}`, again without being saved.
        static::deleting(function (Category $category): void {
            foreach ($category->posts as $post) {
                $post->rememberPath(Post::pathFor($category->slug, $post->slug));
            }
        });
    }

    /**
     * @return array<string, array{source: string, reserved: list<string>}>
     */
    public function sluggable(): array
    {
        return [
            'slug' => ['source' => 'name', 'reserved' => self::RESERVED_SLUGS],
        ];
    }

    /**
     * @return HasMany<Post, $this>
     */
    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function url(): string
    {
        return route('blog.category', $this->slug);
    }

    /** A category with only drafts is hidden: its name can give away an unannounced post. */
    public function isPubliclyVisible(): bool
    {
        return $this->posts()->published()->exists();
    }

    /**
     * @param  Builder<self>  $query
     * @return Builder<self>
     */
    public function scopeWithPublishedPosts(Builder $query): Builder
    {
        return $query->whereHas('posts', fn (Builder $posts) => $posts->published());
    }

    public function toSitemapTag(): Url
    {
        return Url::create($this->url());
    }
}
