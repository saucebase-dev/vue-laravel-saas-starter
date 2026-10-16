# Saucebase Coming Soon

<div align="center">

[![Tests](https://github.com/saucebase-dev/coming-soon/actions/workflows/test.yml/badge.svg)](https://github.com/saucebase-dev/coming-soon/actions/workflows/test.yml)
[![Release](https://img.shields.io/github/v/release/saucebase-dev/coming-soon)](https://github.com/saucebase-dev/coming-soon/releases)
[![Saucebase Core](https://img.shields.io/packagist/dependency-v/saucebase/coming-soon/saucebase%2Fcore?label=Saucebase%20Core&color=FF6B35)](https://github.com/saucebase-dev/core)
[![License](https://img.shields.io/github/license/saucebase-dev/coming-soon)](LICENSE)

</div>

A configurable coming soon page and site access gate for Saucebase.

A module for [Saucebase](https://github.com/saucebase-dev/saucebase), the AI-ready Laravel SaaS kit. Modules are Composer packages you add to a Saucebase app; once installed, the code is yours to change.

## Features

- **Coming Soon page** with an editable title and message
- **Admin toggle** to redirect visitors while the site is under construction
- **Route exceptions** with wildcard support, configured by the application
- **Administrator access**: settings managers can browse the site normally
- **Administrator banner** fixed at the bottom, with a link to Coming Soon settings
- **Vue and React**, with server rendering and light and dark themes

## Install

**New to Saucebase?** Start a new app and pick **coming-soon** when it asks for modules:

```bash
curl -fsSL https://install.saucebase.dev | bash
```

It installs PHP and Composer if they're missing. With the CLI already installed, run `saucebase new my-app` instead.

**Already have a Saucebase app?** Add it with:

```bash
composer require saucebase/coming-soon
php artisan migrate
npm install
npm run build
```

## Configuration

Coming Soon is off by default. Enable it and edit the page text in the admin panel, under **Settings → Coming Soon**.

Set `path` and `except_routes` in the application's `config/coming-soon.php`. The default path is `/coming-soon`; choose a path separate from the homepage. Route exceptions accept names and wildcard patterns. Allow the application's login and admin routes so administrators can still sign in and manage settings.

The route name stays `coming-soon.index`. Rebuild the route cache after changing the path. When disabled, the Coming Soon URL redirects to `/`.

Users with `manage settings` bypass redirects and see the banner. Its settings link is shown only to users who also have `access admin panel`. The module uses Laravel authentication and core permissions; it does not require the Auth module.

Only HTML GET/HEAD and Inertia page visits redirect. APIs, submissions, assets, and unknown URLs keep their normal behavior. This does not replace authentication or Laravel maintenance mode.

## Demo data

This module has no seeders or demo data.

## License

MIT. See [LICENSE](LICENSE).
