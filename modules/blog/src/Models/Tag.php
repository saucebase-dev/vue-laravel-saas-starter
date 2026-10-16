<?php

namespace Modules\Blog\Models;

use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Modules\Blog\Contracts\Redirectable;
use Modules\Blog\Traits\HasRedirects;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property int $id
 * @property string $name
 * @property string $slug
 */
class Tag extends Model implements Redirectable, Sitemapable
{
    use HasFactory, HasRedirects, Sluggable;

    protected $table = 'blog_tags';

    protected $fillable = ['name', 'slug'];

    /**
     * @return array<string, array<string, string>>
     */
    public function sluggable(): array
    {
        return [
            'slug' => ['source' => 'name'],
        ];
    }

    /**
     * @return BelongsToMany<Post, $this>
     */
    public function posts(): BelongsToMany
    {
        return $this->belongsToMany(Post::class, 'blog_post_tag');
    }

    public function url(): string
    {
        return route('blog.tag', $this->slug);
    }

    /** A tag with only drafts is hidden: its name can give away an unannounced post. */
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
