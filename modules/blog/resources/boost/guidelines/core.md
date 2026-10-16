## Blog module

`modules/blog` (namespace `Modules\Blog`) provides posts, categories, tags, cover images, SEO metadata, and
RSS. Public pages render with SSR; the admin lives in Filament under Blog.

- Post content is sanitized on output; never render it raw.
- Changed post, category, and tag URLs 301 automatically through `HasRedirects`; don't add manual redirects.

Activate the `saucebase-blog-development` skill before changing this module.
