# Saucebase Laravel SaaS Starter

A starting point for building a SaaS app with Laravel and Vue. It includes sign-up and login, billing, themes, a blog, a roadmap, announcements, and a coming-soon page.

The planned Stripe Projects setup uses Laravel Cloud to host the app, MySQL to store its data, Stripe for test payments, and Resend to send email. Cloudflare can provide an optional custom domain.

**The app runs locally. The automatic Projects setup is still being built.**

## Get started

You need PHP 8.4+, Composer, Node 22.12+, and npm 10.5.1+.

```bash
composer projects:install
composer projects:dev
```

The first command installs dependencies and builds the frontend. The second prepares the database, adds the starter plans, and starts the app.

You can run setup again without losing users, changing your application key, or replacing plans you have edited.

Local development uses SQLite. Emails are written to the app's log instead of being sent.

If you use Laravel Herd, run `composer projects:prepare`, then use Herd and your usual frontend development command.

## Payments

The app starts with Free and Pro plans. Pro has example prices of 29 per month or 290 per year, in the billing currency you choose. Edit these plans to suit your product.

After Stripe Projects connects a sandbox, send your plans to Stripe:

```bash
php artisan billing:push-catalog
```

To bring later changes from Stripe back into the app:

```bash
php artisan billing:sync-catalog
```

Use a Stripe sandbox while testing. Setup does not create demo users or subscriptions, and it does not publish plans to Stripe automatically.

Stripe also needs to send payment updates back to the app. This is called a webhook. Automatic webhook setup is still unfinished. See the [billing module guide](modules/billing/README.md) for the events it uses.

## Email and API keys

Stripe Projects should create the services and supply their API keys. The template should connect those keys to Laravel automatically, so you do not have to copy them by hand.

That automatic connection still needs to be completed and tested. Projects supplies keys locally; our deployment setup also needs to pass them securely to Laravel Cloud.

For email, you choose a sender address and verify that you own its domain. Resend can then send account verification, password reset, and billing emails.

## Hosting

The app is being prepared for Laravel Cloud with a MySQL database. Our initial region choice is Ireland (`eu-west-1`). When creating your own app, use your own repository and preferred region.

These commands are ready for the Cloud settings:

| Setting | Command |
| --- | --- |
| Build | `composer cloud:build` |
| Deploy | `composer cloud:deploy` |
| Process background jobs | `php artisan queue:work --tries=3` |
| Render pages on the server | `php artisan inertia:start-ssr` |

Enable Cloud's scheduler so regular billing tasks run. Use PHP 8.4+ and a Node version that meets the requirements above.

Cloud should supply the database connection details. The hosted app also needs its public URL, debug mode turned off, and a permanent application key. The deployment command requires that key to be set already.

Store uploaded files in Cloud object storage so they survive deployments. Public uploads, such as avatars and blog images, need a public bucket connected as the `public` disk. Private files need a separate private bucket. Let Cloud configure the disks rather than keeping a local storage override.

The production build has passed in a clean copy. A real Cloud deployment, automatic key delivery, email, payments, and file uploads still need to be tested together.

## Custom domain

You can use an existing domain or buy one through Cloudflare. Connect it to Laravel Cloud and add the DNS records Resend needs for email.

A custom domain is optional for hosting: Cloud gives the app an address to start with. Sending email still requires a verified sender domain.

## Publishing the template

[projects-template.yaml](projects-template.yaml) describes the planned Projects template. It is a draft and has not been published.

Once the full setup works, we will push a tested version to the public repository, add its commit to the template file, and submit that file to Stripe's template registry.

## Development checks

```bash
php artisan test --compact --testsuite=Feature
vendor/bin/pint --dirty --format agent
npm run build
```

Laravel Boost includes guidance for the app and its modules. Refresh it with `composer boost:update`.
