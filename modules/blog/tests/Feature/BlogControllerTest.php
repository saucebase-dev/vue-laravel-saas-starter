<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Database\Events\QueryExecuted;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Tests\TestCase;

class BlogControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_index_returns_only_published_posts(): void
    {
        $published = Post::factory()->published()->create();
        Post::factory()->draft()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('posts.data.0.id', $published->id)
                ->where('posts.data', fn ($posts) => count($posts) === 1)
            );
    }

    public function test_scheduled_post_is_hidden_until_its_publish_date(): void
    {
        $post = Post::factory()->create(['published_at' => now()->addDay()]);

        $this->get(route('blog.index'))
            ->assertInertia(fn ($page) => $page->has('posts.data', 0));
        $this->get(route('blog.show', $post->slug))->assertNotFound();
    }

    public function test_publishing_without_a_date_stamps_the_publish_date(): void
    {
        $post = Post::factory()->draft()->create();

        $post->update(['status' => PostStatus::Published]);

        $this->assertNotNull($post->fresh()->published_at);
    }

    public function test_related_posts_prefer_the_same_category_and_exclude_the_current_post(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);
        $sameCategory = Post::factory()->create([
            'category_id' => $category->id,
            'published_at' => now()->subYear(),
        ]);
        Post::factory(3)->create(['category_id' => null, 'published_at' => now()->subHour()]);

        $this->get(route('blog.show.category', [$category->slug, $post->slug]))
            ->assertInertia(fn ($page) => $page
                ->has('related', 3)
                ->where('related.0.id', $sameCategory->id)
                ->where('related', fn ($related) => collect($related)->doesntContain('id', $post->id))
            );
    }

    public function test_sitemap_lists_the_blog_and_only_published_posts(): void
    {
        $category = Category::factory()->create();
        $published = Post::factory()->published()->create(['category_id' => $category->id]);
        $draft = Post::factory()->draft()->create();

        $this->get(route('sitemap'))
            ->assertOk()
            ->assertSee('<loc>'.route('blog.index').'</loc>', false)
            ->assertSee('<loc>'.route('blog.show.category', [$category->slug, $published->slug]).'</loc>', false)
            ->assertDontSee($draft->slug);
    }

    public function test_sitemap_entry_carries_the_cover_image(): void
    {
        Storage::fake('public');

        $post = Post::factory()->published()->create();

        $this->assertSame([], $post->toSitemapTag()->images);

        $post->addMedia(UploadedFile::fake()->image('cover.jpg'))
            ->toMediaCollection('cover');

        $images = $post->refresh()->toSitemapTag()->images;

        $this->assertCount(1, $images);
        $this->assertSame($post->title, $images[0]->caption);
    }

    public function test_listings_load_the_cover_images_in_one_query(): void
    {
        Post::factory()->published()->count(3)->create();

        $mediaQueries = 0;
        DB::listen(function (QueryExecuted $query) use (&$mediaQueries): void {
            $mediaQueries += (int) preg_match('/from .media./', $query->sql);
        });

        $this->get(route('blog.index'))->assertOk();
        $this->assertSame(1, $mediaQueries);

        $mediaQueries = 0;
        $this->get('/sitemap.xml')->assertOk();
        $this->assertSame(1, $mediaQueries);
    }

    public function test_index_returns_paginated_response(): void
    {
        Post::factory(3)->published()->create();

        $this->get(route('blog.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('posts.data')
                ->has('posts.current_page')
                ->has('posts.last_page')
            );
    }

    public function test_show_resolves_post_by_slug_without_category(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.id', $post->id)
            );
    }

    public function test_show_resolves_post_by_category_and_slug(): void
    {
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $category->id]);

        $this->get(route('blog.show.category', [$category->slug, $post->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.id', $post->id)
            );
    }

    public function test_show_returns_404_for_draft_post(): void
    {
        $post = Post::factory()->draft()->create(['category_id' => null]);

        $this->get(route('blog.show', $post->slug))
            ->assertNotFound();
    }

    public function test_show_returns_404_for_unknown_slug(): void
    {
        $this->get(route('blog.show', 'nonexistent-slug'))
            ->assertNotFound();
    }

    public function test_show_returns_404_when_category_slug_mismatches(): void
    {
        $correctCategory = Category::factory()->create();
        $wrongCategory = Category::factory()->create();
        $post = Post::factory()->published()->create(['category_id' => $correctCategory->id]);

        $this->get(route('blog.show.category', [$wrongCategory->slug, $post->slug]))
            ->assertNotFound();
    }

    public function test_show_strips_scripts_from_post_content(): void
    {
        $post = Post::factory()->published()->create([
            'content' => '<p>Hello</p><script>alert(1)</script><img src="x" onerror="alert(1)">',
        ]);

        $this->get(route('blog.show', $post->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.content', fn (string $content) => str_contains($content, '<p>Hello</p>')
                    && ! str_contains($content, '<script')
                    && ! str_contains($content, 'onerror'))
            );
    }

    public function test_post_resource_includes_expected_fields(): void
    {
        $author = User::factory()->create();
        $category = Category::factory()->create();
        $post = Post::factory()->published()->create([
            'category_id' => $category->id,
            'author_id' => $author->id,
        ]);

        $this->get(route('blog.show.category', [$category->slug, $post->slug]))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->has('post.title')
                ->has('post.content')
                ->has('post.cover_url')
                ->has('post.category.name')
                ->has('post.author.name')
            );
    }

    public function test_category_page_lists_only_its_published_posts(): void
    {
        $category = Category::factory()->create(['name' => 'Guides']);
        $inCategory = Post::factory()->published()->create(['category_id' => $category->id]);
        Post::factory()->draft()->create(['category_id' => $category->id]);
        Post::factory()->published()->create(['category_id' => Category::factory()]);
        Category::factory()->create();

        $this->get(route('blog.category', $category->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('heading.title', 'Guides')
                ->has('posts.data', 1)
                ->where('posts.data.0.id', $inCategory->id)
                ->where('posts.path', route('blog.category', $category->slug))
                ->where('activeCategory', $category->slug)
                ->has('categories', 2)
            );
    }

    public function test_tag_page_lists_only_published_posts_with_that_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'laravel']);
        $tagged = Post::factory()->published()->hasAttached($tag)->create();
        Post::factory()->draft()->hasAttached($tag)->create();
        Post::factory()->published()->create();

        $this->get(route('blog.tag', $tag->slug))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('heading.title', '#laravel')
                ->has('posts.data', 1)
                ->where('posts.data.0.id', $tagged->id)
                ->where('posts.data.0.tags.0.slug', $tag->slug)
            );
    }

    public function test_unknown_category_or_tag_page_is_not_found(): void
    {
        $this->get(route('blog.category', 'nope'))->assertNotFound();
        $this->get(route('blog.tag', 'nope'))->assertNotFound();
    }

    public function test_a_category_or_tag_used_only_by_drafts_is_not_found(): void
    {
        $category = Category::factory()->create(['name' => 'Secret launch']);
        $tag = Tag::factory()->create(['name' => 'secret-launch']);
        Post::factory()->draft()->hasAttached($tag)->create(['category_id' => $category->id]);

        $this->get(route('blog.category', $category->slug))->assertNotFound();
        $this->get(route('blog.tag', $tag->slug))->assertNotFound();
    }

    public function test_only_categories_and_tags_with_published_posts_are_public(): void
    {
        [$live, $hidden] = Category::factory()->count(2)->create();
        [$liveTag, $hiddenTag] = Tag::factory()->count(2)->create();
        Post::factory()->published()->hasAttached($liveTag)->create(['category_id' => $live->id]);
        Post::factory()->draft()->hasAttached($hiddenTag)->create(['category_id' => $hidden->id]);

        $this->assertEquals([$live->id], Category::withPublishedPosts()->pluck('id')->all());
        $this->assertEquals([$liveTag->id], Tag::withPublishedPosts()->pluck('id')->all());
    }

    public function test_related_posts_rank_shared_tags_above_newer_posts(): void
    {
        $tag = Tag::factory()->create();
        $post = Post::factory()->published()->hasAttached($tag)->create(['category_id' => null]);
        $sharesTag = Post::factory()->hasAttached($tag)->create(['category_id' => null, 'published_at' => now()->subYear()]);
        Post::factory(3)->create(['category_id' => null, 'published_at' => now()->subHour()]);

        $this->get(route('blog.show', $post->slug))
            ->assertInertia(fn ($page) => $page->where('related.0.id', $sharesTag->id));
    }

    public function test_sitemap_lists_category_and_tag_pages_only_when_they_have_published_posts(): void
    {
        $category = Category::factory()->create();
        $tag = Tag::factory()->create();
        Post::factory()->published()->hasAttached($tag)->create(['category_id' => $category->id]);

        $emptyCategory = Category::factory()->create();
        $draftOnlyTag = Tag::factory()->create();
        Post::factory()->draft()->hasAttached($draftOnlyTag)->create(['category_id' => $emptyCategory->id]);

        $this->get(route('sitemap'))
            ->assertSee('<loc>'.route('blog.category', $category->slug).'</loc>', false)
            ->assertSee('<loc>'.route('blog.tag', $tag->slug).'</loc>', false)
            ->assertDontSee(route('blog.category', $emptyCategory->slug), false)
            ->assertDontSee(route('blog.tag', $draftOnlyTag->slug), false);
    }

    public function test_a_category_named_after_a_listing_route_keeps_its_posts_reachable(): void
    {
        $category = Category::factory()->create(['name' => 'Tag']);
        $post = Post::factory()->published()->create(['category_id' => $category->id]);

        $this->assertNotSame('tag', $category->slug);
        $this->get($post->url())
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('post.id', $post->id));
    }

    public function test_feed_lists_published_posts_only(): void
    {
        $published = Post::factory()->published()->create();
        $draft = Post::factory()->draft()->create();

        $response = $this->get(route('blog.feed'))->assertOk();

        $this->assertStringContainsString('application/xml', $response->headers->get('Content-Type'));
        $response->assertSee($published->title, false);
        $response->assertDontSee($draft->title, false);
    }
}
