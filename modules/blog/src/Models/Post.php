<?php

namespace Modules\Blog\Models;

use App\Models\User;
use Carbon\Carbon;
use Cviebrock\EloquentSluggable\Sluggable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Modules\Blog\Contracts\Redirectable;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Traits\HasRedirects;
use Spatie\Feed\Feedable;
use Spatie\Feed\FeedItem;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Spatie\Sitemap\Contracts\Sitemapable;
use Spatie\Sitemap\Tags\Url;

/**
 * @property int $id
 * @property string $title
 * @property string $slug
 * @property string $content
 * @property string|null $excerpt
 * @property PostStatus $status
 * @property Carbon|null $published_at
 * @property int|null $category_id
 * @property int|null $author_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
class Post extends Model implements Feedable, HasMedia, Redirectable, Sitemapable
{
    use HasFactory, HasRedirects, InteractsWithMedia, Sluggable;

    protected $table = 'blog_posts';

    protected $fillable = [
        'title',
        'slug',
        'content',
        'excerpt',
        'status',
        'published_at',
        'category_id',
        'author_id',
    ];

    /**
     * Slugs a post cannot take: an uncategorised post lives at `/blog/{post}`, and
     * the feed's URL is matched first.
     */
    public const RESERVED_SLUGS = ['feed'];

    protected static function booted(): void
    {
        static::saving(function (Post $post): void {
            if ($post->status === PostStatus::Published && $post->published_at === null) {
                $post->published_at = now();
            }
        });
    }

    protected function casts(): array
    {
        return [
            'status' => PostStatus::class,
            'published_at' => 'datetime',
        ];
    }

    /**
     * @return array<string, array{source: string, reserved: list<string>}>
     */
    public function sluggable(): array
    {
        return [
            'slug' => ['source' => 'title', 'reserved' => self::RESERVED_SLUGS],
        ];
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('cover')->singleFile();
    }

    public function registerMediaConversions(?Media $media = null): void
    {
        if (! extension_loaded('gd') && ! extension_loaded('imagick')) {
            Log::warning('Blog: skipping the card image conversion because neither the GD nor the Imagick PHP extension is installed. Cards will use the full cover image.');

            return;
        }

        $this->addMediaConversion('card')
            ->performOnCollections('cover')
            ->nonQueued()
            ->width(800)
            ->format('webp');
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return BelongsToMany<Tag, $this>
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'blog_post_tag');
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * The canonical public URL: under the category when the post has one.
     */
    public function url(): string
    {
        return $this->category
            ? route('blog.show.category', [$this->category->slug, $this->slug])
            : route('blog.show', $this->slug);
    }

    /**
     * `scopePublished()` for a post already in memory, without a query: the posts
     * table asks every row. Change the two together.
     */
    public function isPubliclyVisible(): bool
    {
        return $this->status === PostStatus::Published
            && ($this->published_at === null || $this->published_at->isPast());
    }

    /**
     * Where an admin views the post: its public page once it is live, the
     * preview until then.
     */
    public function viewUrl(): string
    {
        return $this->isPubliclyVisible() ? $this->url() : route('blog.preview', $this);
    }

    /**
     * The path a post with this category and slug lives at, e.g. `/blog/news/hello`.
     */
    public static function pathFor(?string $categorySlug, string $slug): string
    {
        return $categorySlug !== null
            ? route('blog.show.category', [$categorySlug, $slug], absolute: false)
            : route('blog.show', $slug, absolute: false);
    }

    public function toSitemapTag(): Url
    {
        $url = Url::create($this->url())->setLastModificationDate($this->updated_at);

        $cover = $this->getFirstMediaUrl('cover');

        return $cover === '' ? $url : $url->addImage($cover, $this->title);
    }

    public function toFeedItem(): FeedItem
    {
        return FeedItem::create()
            ->id($this->url())
            ->title($this->title)
            ->summary($this->excerpt ?? Str::limit(strip_tags($this->content), 300))
            ->updated($this->published_at ?? $this->updated_at)
            ->link($this->url())
            ->authorName($this->author->name ?? '');
    }

    /**
     * Other published posts, closest first: same category, then most tags in
     * common, then newest. A `CASE` rather than `category_id = ?`: a post with
     * no category makes that NULL, which PostgreSQL sorts first. The ID breaks
     * ties, so every database returns the same order.
     *
     * @return Builder<Post>
     */
    public function relatedPosts(): Builder
    {
        return static::published()
            ->whereKeyNot($this->id)
            ->withCount(['tags as shared_tags_count' => fn (Builder $tags) => $tags->whereIn('blog_tags.id', $this->tags->modelKeys())])
            ->when($this->category_id, fn (Builder $query, int $categoryId) => $query->orderByRaw('case when category_id = ? then 0 else 1 end', [$categoryId]))
            ->orderByDesc('shared_tags_count')
            ->orderByDesc('published_at')
            ->orderByDesc('id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->where('status', PostStatus::Published)
            ->where(function (Builder $q): void {
                $q->whereNull('published_at')->orWhere('published_at', '<=', now());
            });
    }
}
