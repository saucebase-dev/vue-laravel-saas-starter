---
name: saucebase-blog-development
description: Work on the Blog module (modules/blog) — posts, categories, tags, cover images, draft preview, RSS, SEO, SSR pages, and its Filament admin. Use when changing blog pages, routes, models, or tests.
---

# Blog Module

Paths are relative to `modules/blog/` unless they say otherwise. Commands run from the application root.

## Overview

Full-featured blog with posts, categories, cover images, author attribution, and SEO metadata. Public pages are SSR-enabled (`->withSSR()`). Filament admin at `/admin` → Blog section.

---

## Non-Obvious Design

### Published Scope

`scopePublished()` requires **both** conditions: `status = Published` AND (`published_at IS NULL` OR `published_at <= now()`). A post with status Published but a future `published_at` is not yet live.

### Dual-Slug Routing

Two routes resolve a post — with and without category prefix:
- `/blog/{slug}` → `blog.show`
- `/blog/{category}/{slug}` → `blog.show.category` (404 when the post is not in that category)

`PostData::url` always points at the category route when the post has one, and the Show page uses it as the canonical URL.

### Category And Tag Pages

A post has **one** category, which owns its URL and canonical, and **many** tags (`blog_post_tag` pivot), which do not. Both are managed in the admin from the posts list (*Manage Categories*, *Manage Tags*); neither is in the navigation. Their pages carry `BreadcrumbsUnderPosts` (`Posts > Tags > …`), and their post counts link to the posts list pre-filtered through the `category` / `tags` table filters, so renaming either filter breaks those links.

- `/blog/category/{category}` → `blog.category`, `/blog/tag/{tag}` → `blog.tag`. Both render `Blog::Index` through `BlogController::listing()`, the same method as the blog index, with a `heading` prop the index leaves `null`. The canonical comes from the paginator's `path`, so one page serves all three.
- They sit under `category/` and `tag/` rather than at `/blog/{category}` so they cannot collide with an uncategorised post at `/blog/{slug}`.
- Registered **above** `/blog/{category}/{slug}`, which would otherwise swallow them. That makes `category`, `tag` and `preview` unusable as category slugs, and `feed` as a post slug: `Category::RESERVED_SLUGS` / `Post::RESERVED_SLUGS` are fed to Sluggable (a generated slug gets a suffix) and to the Filament forms' `notIn()` (a typed one is rejected).
- Related posts rank: same category, then most shared tags, then newest. The rule is `Post::relatedPosts()`; the controller only adds eager loads and the limit.
- A category or tag page is a 404 unless it has a published post: a draft-only tag's name can give away an unannounced post. The sitemap follows the same rule, and so does the category menu (`CategoryNav`) at the top of every listing page. There is deliberately no tag list: tags multiply and most stay thin, so they are reached from posts only.
- The post page opens with breadcrumbs (Blog › category › post) and a matching `BreadcrumbList` JSON-LD, a second `ld+json` script beside the `Article` one.

### Admin Permission

One permission, `manage blog`, covers the whole admin area: `PostResource`, `CategoryResource` and `TagResource` check it in `canAccess()`, and the draft preview checks it too. `access admin panel` alone only opens the panel. `Database\Seeders\DatabaseSeeder` creates it, granted to nobody; the app's roles seeder decides who gets it, and `admin` passes every check.

### Draft Preview

`/blog/preview/{post}` (`blog.preview`, by ID) renders any post, published or not, on the real `Blog::Show` page for anyone with the `manage blog` permission, with a `preview` prop that adds a banner and `noindex`. Everyone else gets a **404**, not a 403, so the link does not confirm the draft exists. It is a separate route rather than a flag on the public URL, so the public route's rules (published only, redirects) stay untouched.

`Post::viewUrl()` picks the public URL once the post is live and the preview until then; the admin's *View Post* / *Preview* action uses it. It relies on `Post::isPubliclyVisible()`, which checks the post's own fields without a query because the posts table asks it for every row, so it must match `scopePublished()`.

`preview` is a reserved category slug, as `/blog/preview/{post}` is matched before `/blog/{category}/{slug}`.

### Old URLs Redirect

Posts, categories and tags keep their old paths in one polymorphic table, `blog_redirects`. Each model uses `HasRedirects` (stores and clears the paths) and implements `Redirectable` (`url()` and `isPubliclyVisible()`).

- **What records a path:** any save that changes a page's URL. `HasRedirects` reads the URL from the database row before the save and compares it with the one after, so it never needs to know which fields shape a URL. The one case it cannot see is a category changing its **posts'** URLs without any post being saved, when its slug changes or it is deleted; `Category::booted()` records those.
- **Where it is read:** `Redirect::responseFor()`, called by `BlogController::show()` when no live post matches, and by the category and tag routes' `->missing()` when route-model binding finds no slug. It 301s to the page's **current** URL.
- A live page always wins: an old path reused by a new post shows the new post.
- No chains: every stored path points at the page, not at the next path, so a post moved three times redirects its first URL straight to its current one.
- A path is stored once; whatever left it last takes it over.
- The target must be publicly visible (a published post, a category or tag with a published post), or the old URL is a 404.
- Deleting a page deletes its redirects, in `HasRedirects`: a morph has no foreign key to cascade.

### Post Card Links

The card's title link stretches over the whole card (`after:absolute after:inset-0`) instead of wrapping it, because the category and tag chips on the card are links too, and a link inside a link is invalid HTML. `PostCategory` and `PostTags` lift their links above the stretched link with `relative z-10`.

### Post Content Is Sanitized on Output

The rich-editor HTML is rendered with `v-html` / `dangerouslySetInnerHTML`. `BlogController::show()` passes it through `Str::sanitizeHtml()` (Filament's Symfony sanitizer) first, so scripts and event handlers never reach the page. Keep that call if the controller changes.

The JSON-LD `<script>` escapes `<` as `<` so a title containing `</script>` cannot break out of it.

### PHP → TypeScript Contract

`Modules\Blog\Data\PostData` (spatie/laravel-data, `#[TypeScript]`) is the contract with the frontend; its types generate into `resources/js/types/generated.d.ts`. `content` is only set on the Show page via `withContent()`.

When adding a frontend field: migration + model `$fillable`, then `PostData`, then regenerate types.

### RSS Feed

`/blog/feed` (`blog.feed`) is RSS 2.0 via spatie/laravel-feed. It is built directly in `BlogController::feed()` — a `Spatie\Feed\Feed` is `Responsable` — rather than through the package's `feed.feeds` config and `Route::feeds()` macro. The macro reads that config while registering routes, which races module config merging, and going without it keeps the app free of a published `config/feed.php`.

The route must stay **above** `/blog/{slug}` in `routes/web.php`, or the catch-all resolves `feed` as a post slug.

`Post::toFeedItem()` reuses `url()`, so feed links match the canonical category URLs.

### Dates

`published_at` is sent as a date string (`2025-04-19`). `PostMeta` formats it with the app's `formatDate()` (`@saucebase/core/lib/dates`), which formats in UTC so it shows the same day on the server render and in every browser time zone.

---

## Testing

```bash
# PHPUnit — this module only
php -d memory_limit=2048M artisan test --compact modules/blog/tests

# E2E
npx playwright test --project="@blog*"
```
