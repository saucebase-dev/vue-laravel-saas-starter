<?php

namespace Modules\Blog\Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Blog\Enums\PostStatus;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Tag;
use Spatie\Permission\Models\Role;

class DemoBlogDatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // A staff account that sees only the blog admin, and writes the demo posts.
        Role::findOrCreate('blog admin')->syncPermissions(['access admin panel', 'manage blog']);
        $author = User::firstOrCreate(
            ['email' => 'blog@saucebase.dev'],
            ['name' => 'Blog Admin', 'password' => bcrypt(config('saucebase.demo_password'))],
        );
        $author->syncRoles('blog admin');

        $gettingStarted = Category::firstOrCreate(['name' => 'Getting Started']);
        $featuresModules = Category::firstOrCreate(['name' => 'Features & Modules']);
        $devExperience = Category::firstOrCreate(['name' => 'Developer Experience']);

        $content = fn (string $file): string => file_get_contents(__DIR__.'/content/'.$file);
        $image = fn (string $file): string => __DIR__.'/../../resources/assets/images/'.$file;

        $this->createPost([
            'title' => 'What Is Saucebase? The Modular Laravel Starter Kit',
            'excerpt' => 'Every SaaS starts the same way: weeks of setup before you write a single line of real product code. Saucebase is the modular Laravel starter kit that skips that part.',
            'content' => $content('post-1-what-is-saucebase.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subMonths(5),
            'category_id' => $gettingStarted->id,
            'author_id' => $author->id,
        ], $image('what-is-saucebase.jpg'), ['Laravel', 'SaaS']);

        $this->createPost([
            'title' => 'Stop Rebuilding the Same Boilerplate Every Project',
            'excerpt' => "If you've launched more than one Laravel app, you know the feeling — two weeks gone before you write any real product code. There's a better way.",
            'content' => $content('post-2-stop-rebuilding-boilerplate.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subMonths(4),
            'category_id' => $gettingStarted->id,
            'author_id' => $author->id,
        ], tags: ['Laravel', 'Productivity']);

        $this->createPost([
            'title' => 'The Modern Laravel Stack: Inertia.js, Vue 3 or React, and Tailwind Done Right',
            'excerpt' => "Modern web dev has a tension between server productivity and rich interactivity. Laravel, Inertia.js, and Tailwind resolve it — and Saucebase wires all the pieces together so you don't have to.",
            'content' => $content('post-3-vilt-stack.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subMonths(3),
            'category_id' => $devExperience->id,
            'author_id' => $author->id,
        ], $image('tech-stack.jpg'), ['Laravel', 'Inertia', 'Vue', 'React', 'Tailwind']);

        $this->createPost([
            'title' => 'Your First Module: Scaffold and Ship in Under 10 Minutes',
            'excerpt' => "One Artisan command scaffolds a fully structured module — service provider, controller, routes, Vue pages, migrations, factory, tests, and Vite config. Here's how it works.",
            'content' => $content('post-4-your-first-module.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subMonths(2),
            'category_id' => $featuresModules->id,
            'author_id' => $author->id,
        ], $image('add-your-saucebase.jpg'), ['Modules', 'Tutorial']);

        $this->createPost([
            'title' => 'Auth, Billing, and Privacy: The Three Modules Every SaaS Needs',
            'excerpt' => "Three modules every SaaS needs — and Saucebase ships them already built. Here's what's included and why they matter.",
            'content' => $content('post-5-auth-billing-privacy.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subMonth(),
            'category_id' => $featuresModules->id,
            'author_id' => $author->id,
        ], $image('cookies-or-privacy.jpg'), ['Modules', 'SaaS', 'Stripe']);

        $this->createPost([
            'title' => 'Copy-and-Own: The Philosophy Behind Saucebase Modules',
            'excerpt' => "Most starter kits give you a locked box. Saucebase takes the opposite approach: when you install a module, the source code lands in your repo and it's yours to read, edit, and own.",
            'content' => $content('post-6-copy-and-own.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subWeeks(2),
            'category_id' => $devExperience->id,
            'author_id' => $author->id,
        ], $image('your-recipes.jpg'), ['Modules']);

        $this->createPost([
            'title' => 'Vue or React? What about both!',
            'excerpt' => 'Choosing a frontend framework used to mean committing to a stack for the life of your project. Saucebase 2.0 changes that — ship with Vue 3 or React 19, and every module works either way.',
            'content' => $content('post-7-vue-and-react.html'),
            'status' => PostStatus::Published,
            'published_at' => now()->subDays(3),
            'category_id' => $devExperience->id,
            'author_id' => $author->id,
        ], $image('vue-and-react.jpg'), ['Vue', 'React', 'Inertia']);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  list<string>  $tags
     */
    private function createPost(array $data, string $coverImage = '', array $tags = []): Post
    {
        $post = Post::firstOrCreate(['title' => $data['title']], $data);

        $post->tags()->syncWithoutDetaching(
            collect($tags)->map(fn (string $name) => Tag::firstOrCreate(['name' => $name])->id)
        );

        if ($coverImage && file_exists($coverImage)) {
            $post->addMedia($coverImage)->preservingOriginal()->toMediaCollection('cover', 'public');
        }

        return $post;
    }
}
