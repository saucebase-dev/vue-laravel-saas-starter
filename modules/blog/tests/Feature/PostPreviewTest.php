<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Filament\Resources\Blog\Pages\EditPost;
use Modules\Blog\Filament\Resources\Blog\Pages\ListPosts;
use Modules\Blog\Models\Post;
use Tests\TestCase;

class PostPreviewTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole('admin');
    }

    public function test_an_admin_can_preview_a_draft(): void
    {
        $draft = Post::factory()->draft()->create();

        $this->actingAs($this->admin)
            ->get(route('blog.preview', $draft))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('post.id', $draft->id)
                ->where('preview', true)
            );
    }

    public function test_an_admin_can_preview_a_scheduled_post(): void
    {
        $scheduled = Post::factory()->published()->create(['published_at' => now()->addWeek()]);

        $this->actingAs($this->admin)
            ->get(route('blog.preview', $scheduled))
            ->assertOk();
    }

    public function test_guests_and_non_admins_cannot_preview(): void
    {
        $draft = Post::factory()->draft()->create();
        $user = User::factory()->create(['email_verified_at' => now()]);
        $user->assignRole('user');

        $this->get(route('blog.preview', $draft))->assertNotFound();
        $this->actingAs($user)->get(route('blog.preview', $draft))->assertNotFound();
    }

    public function test_the_public_page_is_not_marked_as_a_preview(): void
    {
        $post = Post::factory()->published()->create(['category_id' => null]);

        $this->get(route('blog.show', $post->slug))
            ->assertInertia(fn ($page) => $page->where('preview', false));
    }

    public function test_the_admin_view_action_opens_the_preview_until_the_post_is_live(): void
    {
        $post = Post::factory()->draft()->create(['category_id' => null]);

        $this->actingAs($this->admin);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->assertActionHasUrl('view_post', route('blog.preview', $post));

        $post->update(['status' => PostStatus::Published, 'published_at' => now()->subMinute()]);

        Livewire::test(EditPost::class, ['record' => $post->id])
            ->assertActionHasUrl('view_post', $post->url());
    }

    public function test_the_posts_list_view_action_opens_the_preview_until_the_post_is_live(): void
    {
        $draft = Post::factory()->draft()->create(['category_id' => null]);
        $live = Post::factory()->published()->create(['category_id' => null]);

        $this->actingAs($this->admin);

        Livewire::test(ListPosts::class)
            ->assertTableActionHasUrl('view_post', route('blog.preview', $draft), $draft)
            ->assertTableActionHasUrl('view_post', $live->url(), $live);
    }
}
