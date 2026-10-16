<?php

namespace Modules\Blog\Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Redirect;
use Modules\Blog\Models\Tag;
use Tests\TestCase;

class RedirectTest extends TestCase
{
    use RefreshDatabase;

    public function test_a_renamed_slug_permanently_redirects_to_the_new_url(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null, 'slug' => 'old-slug']);

        $post->update(['slug' => 'new-slug']);

        $this->get('/blog/old-slug')->assertRedirect(route('blog.show', 'new-slug'))->assertStatus(301);
    }

    public function test_moving_a_post_to_another_category_redirects_the_old_url(): void
    {
        $news = Category::factory()->create(['slug' => 'news']);
        $guides = Category::factory()->create(['slug' => 'guides']);
        $post = Post::factory()->published()->create(['category_id' => $news->id, 'slug' => 'hello']);

        $post->update(['category_id' => $guides->id]);

        $this->get('/blog/news/hello')->assertStatus(301)->assertRedirect(route('blog.show.category', ['guides', 'hello']));
    }

    public function test_renaming_a_category_redirects_the_urls_of_its_posts(): void
    {
        $category = Category::factory()->create(['slug' => 'news']);
        Post::factory()->published()->create(['category_id' => $category->id, 'slug' => 'hello']);

        $category->update(['slug' => 'updates']);

        $this->get('/blog/news/hello')->assertStatus(301)->assertRedirect(route('blog.show.category', ['updates', 'hello']));
    }

    public function test_deleting_a_category_redirects_the_urls_of_its_posts(): void
    {
        $category = Category::factory()->create(['slug' => 'news']);
        Post::factory()->published()->create(['category_id' => $category->id, 'slug' => 'hello']);

        $category->delete();

        $this->get('/blog/news/hello')->assertStatus(301)->assertRedirect(route('blog.show', 'hello'));
    }

    public function test_renaming_a_category_redirects_its_own_page(): void
    {
        $category = Category::factory()->create(['slug' => 'news']);
        Post::factory()->published()->create(['category_id' => $category->id]);

        $category->update(['slug' => 'updates']);

        $this->get('/blog/category/news')->assertStatus(301)->assertRedirect(route('blog.category', 'updates'));
    }

    public function test_renaming_a_tag_redirects_its_page(): void
    {
        $tag = Tag::factory()->create(['slug' => 'php']);
        Post::factory()->published()->hasAttached($tag)->create();

        $tag->update(['slug' => 'php-8']);

        $this->get('/blog/tag/php')->assertStatus(301)->assertRedirect(route('blog.tag', 'php-8'));
    }

    public function test_deleting_a_page_forgets_its_old_paths(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null, 'slug' => 'old-slug']);
        $post->update(['slug' => 'new-slug']);

        $post->delete();

        $this->assertSame(0, Redirect::count());
        $this->get('/blog/old-slug')->assertNotFound();
    }

    public function test_an_old_url_goes_straight_to_the_current_one_after_several_moves(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null, 'slug' => 'first']);

        $post->update(['slug' => 'second']);
        $post->update(['slug' => 'third']);

        $this->get('/blog/first')->assertStatus(301)->assertRedirect(route('blog.show', 'third'));
    }

    public function test_a_live_post_at_an_old_path_is_shown_rather_than_redirected(): void
    {
        $moved = Post::factory()->published()->create(['category_id' => null, 'slug' => 'taken']);
        $moved->update(['slug' => 'elsewhere']);
        $newcomer = Post::factory()->published()->create(['category_id' => null, 'slug' => 'taken']);

        $this->get('/blog/taken')
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('post.id', $newcomer->id));
    }

    public function test_an_old_url_of_a_post_no_longer_published_is_not_found(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null, 'slug' => 'old-slug']);
        $post->update(['slug' => 'new-slug', 'status' => PostStatus::Draft]);

        $this->get('/blog/old-slug')->assertNotFound();
    }
}
