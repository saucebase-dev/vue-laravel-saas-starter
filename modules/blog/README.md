# Saucebase Blog

<div align="center">

[![Tests](https://github.com/saucebase-dev/blog/actions/workflows/test.yml/badge.svg)](https://github.com/saucebase-dev/blog/actions/workflows/test.yml)
[![Release](https://img.shields.io/github/v/release/saucebase-dev/blog)](https://github.com/saucebase-dev/blog/releases)
[![Saucebase Core](https://img.shields.io/packagist/dependency-v/saucebase/blog/saucebase%2Fcore?label=Saucebase%20Core&color=FF6B35)](https://github.com/saucebase-dev/core)
[![License](https://img.shields.io/github/license/saucebase-dev/blog)](LICENSE)

</div>

A blog for Saucebase: posts, categories, tags, RSS and SEO, managed from the admin.

A module for [Saucebase](https://github.com/saucebase-dev/saucebase), the AI-ready Laravel SaaS kit. Modules are Composer packages you add to a Saucebase app; once installed, the code is yours to change.

## Features

- **Posts** with a rich text editor, cover image, excerpt and author
- **Categories and tags**, each with its own page
- **Scheduling**: posts go live on their publish date
- **Draft preview** on the real page before publishing
- **Old links keep working**: changed slugs redirect to the new URL
- **Built for search**: server-rendered pages, meta tags, breadcrumbs and structured data
- **RSS feed** at `/blog/feed`
- **Safe by default**: post HTML is cleaned before it reaches the page
- **Admin panel** for posts, categories and tags
- **Vue and React**

## Install

**New to Saucebase?** Start a new app and pick **blog** when it asks for modules:

```bash
curl -fsSL https://install.saucebase.dev | bash
```

It installs PHP and Composer if they're missing. With the CLI already installed, run `saucebase new my-app` instead.

**Already have a Saucebase app?** Add it with:

```bash
composer require saucebase/blog
php artisan migrate
php artisan modules:seed --module=blog
npm run build
```

## Configuration

Nothing to set up. Everything is in the admin panel, under **Blog**.

## Demo data

```bash
php artisan modules:seed --module=blog --demo
```

Adds sample posts, categories and tags, and a staff account (`blog@saucebase.dev`) that only sees the blog in the admin. It uses the `DEMO_PASSWORD` from `.env`.

## License

MIT. See [LICENSE](LICENSE).
