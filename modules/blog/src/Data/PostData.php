<?php

namespace Modules\Blog\Data;

use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Spatie\LaravelData\Data;
use Spatie\TypeScriptTransformer\Attributes\TypeScript;

#[TypeScript]
final class PostData extends Data
{
    public function __construct(
        public int $id,
        public string $title,
        public string $slug,
        public ?string $excerpt,
        public string $cover_url,
        public string $card_url,
        public ?string $published_at,
        public ?string $updated_at,
        public ?CategoryData $category,
        /** @var TagData[] */
        public array $tags,
        public ?AuthorData $author,
        public string $url,
        public ?string $content = null,
    ) {}

    /** @param  string|null  $content  sanitized HTML, sent only to the post's own page */
    public static function fromPost(Post $post, ?string $content = null): static
    {
        $cover = $post->getFirstMedia('cover');

        return new self(
            id: $post->id,
            title: $post->title,
            slug: $post->slug,
            excerpt: $post->excerpt,
            cover_url: $post->getFirstMediaUrl('cover'),
            card_url: $cover?->hasGeneratedConversion('card')
                ? $cover->getUrl('card')
                : $post->getFirstMediaUrl('cover'),
            published_at: $post->published_at?->toDateString(),
            updated_at: $post->updated_at?->toDateString(),
            category: $post->category ? CategoryData::from($post->category) : null,
            tags: $post->tags->map(fn (Tag $tag) => TagData::from($tag))->all(),
            author: $post->author ? AuthorData::from($post->author) : null,
            url: $post->url(),
            content: $content,
        );
    }
}
