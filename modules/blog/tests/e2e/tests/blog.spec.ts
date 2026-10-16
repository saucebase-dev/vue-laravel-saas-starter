import { expect, test } from '@e2e/fixtures';

test.describe('Blog public pages', () => {
    test.describe.configure({ mode: 'serial' });

    test('opens a published post from the index and returns', async ({
        page,
        laravel,
    }) => {
        const post = await laravel.factory('Modules\\Blog\\Models\\Post', {
            category_id: null,
            published_at: new Date(Date.now() - 60_000).toISOString(),
        });

        await page.goto('/blog');
        await expect(page).toHaveTitle(/Blog/);

        await page.getByTestId(`post-card-${post.id}`).click();
        await expect(page.getByTestId('post-title')).toBeVisible();
        await expect(page.getByTestId('post-content')).toBeVisible();

        await page.getByTestId('back-to-blog').click();
        await expect(page).toHaveURL(/\/blog$/);
    });

    test('post page publishes canonical and structured data', async ({
        page,
        laravel,
    }) => {
        const post = await laravel.factory('Modules\\Blog\\Models\\Post', {
            category_id: null,
            title: 'Structured </script> title',
            published_at: new Date(Date.now() - 60_000).toISOString(),
        });

        await page.goto('/blog');
        await page.getByTestId(`post-card-${post.id}`).click();
        await expect(page.getByTestId('post-title')).toBeVisible();

        const canonical = page.locator('link[rel="canonical"]');
        await expect(canonical).toHaveAttribute('href', page.url());

        const blocks = (
            await page
                .locator('script[type="application/ld+json"]')
                .allTextContents()
        ).map((text) => JSON.parse(text));
        const jsonLd = blocks.find((block) => block['@type'] === 'Article');
        const breadcrumbs = blocks.find(
            (block) => block['@type'] === 'BreadcrumbList',
        );

        expect(jsonLd['@type']).toBe('Article');
        expect(jsonLd.headline).toBe('Structured </script> title');
        expect(jsonLd.url).toBe(page.url());

        expect(breadcrumbs.itemListElement.at(-1).item).toBe(page.url());
    });

    test('a post links to its category and tag pages', async ({
        page,
        laravel,
    }) => {
        const category = await laravel.factory(
            'Modules\\Blog\\Models\\Category',
        );
        const tag = await laravel.factory('Modules\\Blog\\Models\\Tag');
        const post = await laravel.factory('Modules\\Blog\\Models\\Post', {
            category_id: category.id,
            published_at: new Date(Date.now() - 60_000).toISOString(),
        });
        await laravel.query(
            'INSERT INTO blog_post_tag (post_id, tag_id) VALUES (?, ?)',
            [post.id, tag.id],
        );
        const postUrl = `/blog/${category.slug}/${post.slug}`;

        await page.goto(postUrl);
        await page.getByTestId('post-category-link').click();
        await expect(
            page.getByTestId(`category-nav-${category.slug}`),
        ).toHaveAttribute('aria-current', 'page');
        await expect(page).toHaveURL(`/blog/category/${category.slug}`);
        await expect(page.getByTestId(`post-card-${post.id}`)).toBeVisible();

        await page.goto(postUrl);
        await page.getByTestId(`post-tag-${tag.slug}`).click();
        await expect(page).toHaveURL(`/blog/tag/${tag.slug}`);
        await expect(page.getByTestId(`post-card-${post.id}`)).toBeVisible();
    });

    test('paginated index pages carry their own canonical URL', async ({
        page,
        laravel,
    }) => {
        await laravel.factory(
            'Modules\\Blog\\Models\\Post',
            {
                category_id: null,
                published_at: new Date(Date.now() - 60_000).toISOString(),
            },
            13,
        );

        await page.goto('/blog');
        await expect(page.locator('link[rel="canonical"]')).toHaveAttribute(
            'href',
            /\/blog$/,
        );

        await page.getByTestId('pagination-next').click();
        await expect(page).toHaveURL(/\/blog\?page=2$/);
        await expect(page.locator('link[rel="canonical"]')).toHaveAttribute(
            'href',
            /\/blog\?page=2$/,
        );
        await expect(page.getByTestId('pagination-previous')).toBeVisible();
    });
});
