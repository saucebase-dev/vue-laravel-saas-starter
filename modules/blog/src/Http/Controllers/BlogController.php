<?php

namespace Modules\Blog\Http\Controllers;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;
use Modules\Blog\Data\CategoryData;
use Modules\Blog\Data\PostData;
use Modules\Blog\Models\Category;
use Modules\Blog\Models\Post;
use Modules\Blog\Models\Redirect;
use Modules\Blog\Models\Tag;
use Saucebase\Core\Settings\GeneralSettings;
use Spatie\Feed\Feed;

class BlogController
{
    public function index(): Response
    {
        return $this->listing(Post::published());
    }

    public function category(Category $category): Response
    {
        abort_unless($category->isPubliclyVisible(), 404);

        return $this->listing(Post::published()->whereBelongsTo($category), [
            'title' => $category->name,
            'description' => __('Posts filed under :name.', ['name' => $category->name]),
        ], $category);
    }

    public function tag(Tag $tag): Response
    {
        abort_unless($tag->isPubliclyVisible(), 404);

        return $this->listing(Post::published()->whereRelation('tags', 'blog_tags.id', $tag->id), [
            'title' => '#'.$tag->name,
            'description' => __('Posts tagged :name.', ['name' => $tag->name]),
        ]);
    }

    /**
     * One listing page for the whole blog, a category and a tag, so the three
     * cannot drift apart.
     *
     * @param  Builder<Post>  $posts
     * @param  array{title: string, description: string}|null  $heading  null on the blog index, which the page titles itself
     * @param  Category|null  $activeCategory  the category being listed, highlighted in the category menu
     */
    private function listing(Builder $posts, ?array $heading = null, ?Category $activeCategory = null): Response
    {
        $posts = $posts
            ->with(['category', 'tags', 'author', 'media'])
            ->orderByDesc('published_at')
            ->paginate(12);

        return Inertia::render('Blog::Index', [
            'posts' => $posts->through(fn (Post $post) => PostData::fromPost($post)),
            'heading' => $heading,
            // Only categories with something to read, the same rule the sitemap uses.
            'categories' => Category::withPublishedPosts()
                ->orderBy('name')
                ->get()
                ->map(fn (Category $category) => CategoryData::from($category)),
            'activeCategory' => $activeCategory?->slug,
        ])->withSSR();
    }

    /**
     * The RSS feed of published posts.
     *
     * Built here rather than through the package's `feed.feeds` config and route
     * macro: the module owns its route, and the app publishes no feed config.
     */
    public function feed(GeneralSettings $settings): Feed
    {
        $posts = Post::published()
            ->with(['category', 'author', 'media'])
            ->orderByDesc('published_at')
            ->limit(50)
            ->get();

        return new Feed(
            title: $settings->site_name,
            items: $posts,
            url: route('blog.feed'),
            view: 'feed::rss',
            description: $settings->site_description ?? $settings->site_tagline ?? __('Blog'),
            language: app()->getLocale(),
            format: 'rss',
        );
    }

    /**
     * Any post, published or not, on its real page, for whoever can edit it.
     *
     * A 404 for everyone else rather than a 403, so the link does not even
     * confirm that the draft exists.
     */
    public function preview(Request $request, Post $post): Response
    {
        abort_unless($request->user()?->can('manage blog'), 404);

        return $this->render($post->load(['category', 'tags', 'author', 'media']), preview: true);
    }

    public function show(Request $request, string $categoryOrSlug, ?string $slug = null): Response|RedirectResponse
    {
        $query = Post::published()->with(['category', 'tags', 'author', 'media']);

        $post = $slug !== null
            ? $query->whereRelation('category', 'slug', $categoryOrSlug)->where('slug', $slug)->first()
            : $query->where('slug', $categoryOrSlug)->first();

        if ($post === null) {
            return Redirect::responseFor($request);
        }

        return $this->render($post);
    }

    /**
     * @param  bool  $preview  shown to an admin before the post is public: marked as such and kept out of search
     */
    private function render(Post $post, bool $preview = false): Response
    {
        $related = $post->relatedPosts()
            ->with(['category', 'tags', 'author', 'media'])
            ->limit(3)
            ->get()
            ->map(fn (Post $p) => PostData::fromPost($p));

        return Inertia::render('Blog::Show', [
            'post' => PostData::fromPost($post, content: Str::sanitizeHtml($post->content)),
            'related' => $related,
            'preview' => $preview,
        ])->withSSR();
    }
}
