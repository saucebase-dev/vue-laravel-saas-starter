<laravel-boost-guidelines>
=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.4. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.
- Activate the `deploying-to-cloud` skill whenever deploying to Laravel Cloud, configuring Cloud environments or resources, using the Cloud CLI, or troubleshooting Cloud deployments.

=== herd rules ===

# Laravel Herd

- The application is served by Laravel Herd at `https?://[kebab-case-project-dir].test`. Use the `get-absolute-url` tool to generate valid URLs. Never run commands to serve the site. It is always available.
- Use the `herd` CLI to manage services, PHP versions, and sites (e.g. `herd sites`, `herd services:start <service>`, `herd php:list`). Run `herd list` to discover all available commands.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== phpunit/core rules ===

# PHPUnit

- This project uses PHPUnit. Create tests with `php artisan make:test --phpunit {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/phpunit` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-vue/core rules ===

# Inertia + Vue

Vue components must have a single root element.
- IMPORTANT: Activate `inertia-vue-development` when working with Inertia Vue client-side patterns.

=== saucebase/announcements/core rules ===

## Announcements module

`modules/announcements` (namespace `Modules\Announcements`) shows site-wide announcement banners with
scheduling, audience targeting, and cookie-based dismissal. It is admin-only: no pages, the banner mounts in
the `top` global component slot.

- The server removes a dismissed banner, not the click; never hide it client-side first.
- `show_on_frontend` and `show_on_dashboard` are independent audiences.

Activate the `saucebase-announcements-development` skill before changing this module.

=== saucebase/auth/core rules ===

## Auth module

`modules/auth` (namespace `Modules\Auth`) owns login, registration, magic link, password reset,
email verification, Socialite OAuth, impersonation, and the Profile and Security settings panels.

- Account settings routes keep their `/settings/*` URLs and `settings.*` names; core resolves them by name.
- `email_verified_at` is never mass-assignable; set it with `forceFill()`.
- Login and register are canonical pages that open as a modal only when the request carries the modal header.

Activate the `saucebase-auth-development` skill before changing this module.

=== saucebase/billing/core rules ===

## Billing module

`modules/billing` (namespace `Modules\Billing`) handles checkout, subscriptions, payments, invoices, and
webhooks through a payment gateway driver (Stripe by default).

- Look up provider rows by `(provider, provider_*_id)`, never the provider ID alone.
- `Price::amount` is in minor units (cents); format with `Currency::formatAmount()`.
- Delete billing owners through the model, never a bulk query; billing history outlives the account.
- Raw SQL must work on SQLite, MySQL, and PostgreSQL.

Activate the `saucebase-billing-development` skill before changing this module.

=== saucebase/blog/core rules ===

## Blog module

`modules/blog` (namespace `Modules\Blog`) provides posts, categories, tags, cover images, SEO metadata, and
RSS. Public pages render with SSR; the admin lives in Filament under Blog.

- Post content is sanitized on output; never render it raw.
- Changed post, category, and tag URLs 301 automatically through `HasRedirects`; don't add manual redirects.

Activate the `saucebase-blog-development` skill before changing this module.

=== saucebase/coming-soon/core rules ===

## Coming Soon module

`modules/coming-soon` (namespace `Modules\ComingSoon`) owns the Coming Soon page,
site-wide settings, browser-page redirects, and the settings-manager banner.

- Application config owns route exceptions; never depend on another module.
- Keep `coming-soon.index` stable; its path comes from `coming-soon.path`.
- Only HTML GET/HEAD and Inertia visits redirect. Settings managers bypass them.

Activate `saucebase-coming-soon-development` before changing this module.

=== saucebase/core/core rules ===

## Saucebase core

`saucebase/core` (namespace `Saucebase\Core`) loads modules and provides what they build on: the module
service provider, navigation, breadcrumbs, the settings modal, the sitemap, toasts, and the Filament
module plugin.

- Modules are copy-and-own Composer packages in lowercase `modules/<name>/` directories, with TitleCase
  namespaces. An installed module is active; there is no enable/disable toggle.
- A module's main provider extends `Saucebase\Core\Providers\ModuleServiceProvider` without `$name` or
  `$nameLower`; the base provider looks the name up with `ModuleRegistry::moduleForClass()`.
- Follow Laravel's naming and placement unless a rule here says otherwise: exceptions end in
  `Exception`, commands in `Command`, form requests in `Request`.
- Traits live in `Traits/`, never `Concerns/`, including internal helpers (`Filament/Traits/`,
  `Console/Traits/`), so the directory says what the file is.
- A module's policy for its own model lives in `src/Policies/<Model>Policy.php`, where Laravel
  discovers it; never register it with `Gate::policy()`. Register only a policy for a model the
  module does not own, such as auth's `UserPolicy` for the app's `App\Models\User`.
- A module's agent context lives in its `resources/boost/`. `composer boost:update` runs `modules:boost`,
  which links each module into the vendor path Boost scans and lists it in `boost.json`, so never list
  modules there by hand.
- Authorization follows spatie/laravel-permission: check **permissions**, never role names
  (`permission:…`, `can('access admin panel')`). Signing in is the only gate for the signed-in
  area; a permission guards only what some signed-in users must not reach. `access admin panel`
  opens the panel; each module's admin area is its own `manage {module}` permission, checked in
  `canAccess()` and created by the module's `DatabaseSeeder`, and site-wide settings pages are
  `manage settings`. The `admin` role passes every check through core's
  `Gate::before`. Roles are defined in core's `RolesDatabaseSeeder`; `admin` and `user` (the role
  every sign-up gets) are fixed names modules may assign. The one exception is demo data: a module's
  `Demo…DatabaseSeeder` may create a staff role for its own area (`blog admin`, `blog@saucebase.dev`).
- Where a signed-in user lands is the app's choice: `Home::using(fn (Request $request, string $reason) => …)`
  in a service provider (null means the site root). Modules redirect with `Home::url($request, Home::LOGIN)`
  or link to `route('home')`, never `route('dashboard')`, which is only the dashboard page.
- Translation: text people read goes through `__()` / `t()` with the English sentence as its
  JSON key; keyed server messages (`auth::auth.failed`) live in a module's `lang/<locale>/*.php`.
  Developer console output and exceptions stay plain English. Never list keys by hand:
  `translatable:export <locale>` (configured by core to scan the app and every module) writes
  `lang/<locale>.json`. A locale is discovered from `lang/<locale>/` or `lang/<locale>.json`
  (`LocalizationSettings::available()`) and offered once enabled in the admin. The frontend
  bundles `lang/*.json` at build time, so a new language reaches the browser only after a build.

Activate `saucebase-module-development` before creating or changing a module, and
`saucebase-filament-development` before changing a module's Filament code.

=== saucebase/roadmap/core rules ===

## Roadmap module

`modules/roadmap` (namespace `Modules\Roadmap`) is a public feature-request board with upvotes, comments,
team replies, and duplicate merging. The admin lives in Filament under Roadmap.

- Moderation state and roadmap stage are separate statuses.
- The board is public; voting and commenting need a signed-in user.

Activate the `saucebase-roadmap-development` skill before changing this module.

=== saucebase/themes/core rules ===

## Themes module

`modules/themes` (namespace `Modules\Themes`) is a visual theme editor. **Set as default** (or
`php artisan saucebase:theme:apply {theme}`) rewrites `resources/css/theme.css` and
`resources/themes/default.json`; commit both.

- Theme logic lives in the module's `resources/js/lib/`, with no UI-framework, Inertia, or app imports.

Activate the `saucebase-themes-development` skill before changing this module.

</laravel-boost-guidelines>
