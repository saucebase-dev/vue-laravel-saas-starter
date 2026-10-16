<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Modules\Blog\Filament\Resources\Blog\Pages\CreateTag;
use Modules\Blog\Filament\Resources\Blog\Pages\EditTag;
use Modules\Blog\Filament\Resources\Blog\Pages\ListTags;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Tests\TestCase;

class TagResourceTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['email_verified_at' => now()]);
        $this->admin->assignRole('admin');
    }

    public function test_admin_can_list_tags(): void
    {
        $tags = Tag::factory(3)->create();

        $this->actingAs($this->admin);

        Livewire::test(ListTags::class)
            ->assertCanSeeTableRecords($tags);
    }

    public function test_admin_can_create_tag(): void
    {
        $this->actingAs($this->admin);

        Livewire::test(CreateTag::class)
            ->fillForm(['name' => 'Laravel'])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertDatabaseHas('blog_tags', ['name' => 'Laravel', 'slug' => 'laravel']);
    }

    public function test_changing_a_slug_in_the_admin_redirects_the_old_tag_page(): void
    {
        $tag = Tag::factory()->create(['slug' => 'php']);
        Post::factory()->published()->hasAttached($tag)->create();

        $this->actingAs($this->admin);

        Livewire::test(EditTag::class, ['record' => $tag->id])
            ->fillForm(['slug' => 'php-8'])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->get('/blog/tag/php')->assertStatus(301)->assertRedirect(route('blog.tag', 'php-8'));
    }

    public function test_admin_can_delete_tag(): void
    {
        $tag = Tag::factory()->create();

        $this->actingAs($this->admin);

        Livewire::test(EditTag::class, ['record' => $tag->id])
            ->callAction('delete');

        $this->assertDatabaseMissing('blog_tags', ['id' => $tag->id]);
    }
}
