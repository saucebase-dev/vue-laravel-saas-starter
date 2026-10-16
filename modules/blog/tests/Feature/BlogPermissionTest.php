<?php

namespace Modules\Blog\Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Modules\Blog\Database\Seeders\DatabaseSeeder;
use Modules\Blog\Filament\Resources\Blog\CategoryResource;
use Modules\Blog\Filament\Resources\Blog\PostResource;
use Modules\Blog\Filament\Resources\Blog\TagResource;
use Modules\Blog\Models\Post;
use PHPUnit\Framework\Attributes\DataProvider;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

/**
 * The blog admin area is its own permission, so a role can be given the blog and nothing
 * else in the panel. `access admin panel` alone only opens the door.
 */
class BlogPermissionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(DatabaseSeeder::class);
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function resources(): array
    {
        return [
            'posts' => [PostResource::class],
            'categories' => [CategoryResource::class],
            'tags' => [TagResource::class],
        ];
    }

    public function test_the_seeder_creates_the_permission(): void
    {
        $this->assertTrue(Permission::where('name', 'manage blog')->exists());
    }

    #[DataProvider('resources')]
    public function test_a_blog_admin_can_open_the_blog(string $resource): void
    {
        $this->actingAs($this->staff('access admin panel', 'manage blog'))
            ->get($resource::getUrl('index'))
            ->assertOk();
    }

    #[DataProvider('resources')]
    public function test_panel_access_alone_does_not_open_the_blog(string $resource): void
    {
        $this->actingAs($this->staff('access admin panel'))
            ->get($resource::getUrl('index'))
            ->assertForbidden();
    }

    public function test_only_a_blog_admin_can_preview_a_draft(): void
    {
        $draft = Post::factory()->create();

        $this->actingAs($this->staff('access admin panel'))
            ->get(route('blog.preview', $draft))
            ->assertNotFound();

        $this->actingAs($this->staff('access admin panel', 'manage blog'))
            ->get(route('blog.preview', $draft))
            ->assertOk();
    }

    private function staff(string ...$permissions): User
    {
        Permission::findOrCreate('access admin panel');

        return User::factory()->create(['email_verified_at' => now()])->givePermissionTo($permissions);
    }
}
