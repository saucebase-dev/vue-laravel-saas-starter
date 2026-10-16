<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Filament\Resources\Blog\Pages\CreatePost;
use Modules\Blog\Filament\Resources\Blog\Pages\EditPost;
use Modules\Blog\Filament\Resources\Blog\Pages\ListPosts;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Tests\TestCase;

class PostResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_list_posts(): void
    {
        $posts = Post::factory(3)->create();

        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)
            ->assertCanSeeTableRecords($posts);
    }

    public function test_admin_can_create_post_with_author_defaulting_to_self(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreatePost::class)
            ->fillForm([
                'title' => 'Test Post',
                'content' => '<p>Test content</p>',
                'status' => PostStatus::Published->value,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_posts', [
            'title' => 'Test Post',
            'author_id' => $this->admin->id,
        ]);
    }

    public function test_admin_can_tag_a_post(): void
    {
        $post = Post::factory()->create();
        $tags = Tag::factory(2)->create();

        $this->actingAs($this->admin);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->fillForm(['tags' => $tags->modelKeys()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertEqualsCanonicalizing($tags->modelKeys(), $post->tags()->pluck('blog_tags.id')->all());
    }

    public function test_the_category_post_count_link_lists_only_that_categorys_posts(): void
    {
        $category = Category::factory()->create();
        $inCategory = Post::factory()->create(['category_id' => $category->id]);
        $elsewhere = Post::factory()->create(['category_id' => null]);

        $this->actingAs($this->admin);

        Livewire::withQueryParams(['filters' => ['category' => ['value' => $category->id]]])
            ->test(ListPosts::class)
            ->assertCanSeeTableRecords([$inCategory])
            ->assertCanNotSeeTableRecords([$elsewhere]);
    }

    public function test_the_tag_post_count_link_lists_only_that_tags_posts(): void
    {
        $tag = Tag::factory()->create();
        $tagged = Post::factory()->hasAttached($tag)->create();
        $untagged = Post::factory()->create();

        $this->actingAs($this->admin);

        Livewire::withQueryParams(['filters' => ['tags' => ['values' => [$tag->id]]]])
            ->test(ListPosts::class)
            ->assertCanSeeTableRecords([$tagged])
            ->assertCanNotSeeTableRecords([$untagged]);
    }

    public function test_admin_can_edit_post(): void
    {
        $post = Post::factory()->create(['author_id' => $this->admin->id]);

        $this->actingAs($this->admin);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->fillForm(['title' => 'Updated Title'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_posts', ['id' => $post->id, 'title' => 'Updated Title']);
    }

    public function test_admin_can_delete_post(): void
    {
        $post = Post::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('blog_posts', ['id' => $post->id]);
    }

    public function test_status_filter_works(): void
    {
        $published = Post::factory()->published()->create();
        $draft = Post::factory()->draft()->create();

        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)
            ->filterTable('status', PostStatus::Draft->value)
            ->assertCanSeeTableRecords([$draft])
            ->assertCanNotSeeTableRecords([$published]);
    }
}
