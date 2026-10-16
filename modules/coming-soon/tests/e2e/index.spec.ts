import { expect, test } from '@e2e/fixtures';

test('settings toggle the visitor page and administrator banner', async ({
    page,
    browser,
    laravel,
    credentials,
    loginAs,
}) => {
    const helper = String.raw`Modules\ComingSoon\Tests\Support\ComingSoonTestHelper`;
    const original = await laravel.callFunction(`${helper}::snapshot`);
    const urls = await laravel.callFunction<{ page: string; settings: string }>(
        `${helper}::urls`,
    );
    const visitor = await browser.newContext({
        ignoreHTTPSErrors: true,
        baseURL: new URL(urls.page).origin,
    });
    const visitorPage = await visitor.newPage();
    try {
        await loginAs(credentials.admin);
        await page.goto(urls.settings);
        await page.getByTestId('coming-soon-enabled').setChecked(true);
        await page
            .getByTestId('coming-soon-title-input')
            .fill('Launching soon');
        await page
            .getByTestId('coming-soon-message-input')
            .fill('<strong>See you soon.</strong>');
        await page.getByTestId('coming-soon-save').click();
        await expect
            .poll(
                async () =>
                    (
                        await laravel.callFunction<{ enabled: boolean }>(
                            `${helper}::snapshot`,
                        )
                    ).enabled,
            )
            .toBe(true);

        await visitorPage.goto('/');
        await expect(visitorPage).toHaveURL(urls.page);
        await expect(visitorPage.getByTestId('coming-soon-title')).toHaveText(
            'Launching soon',
        );
        await expect(visitorPage.getByTestId('coming-soon-message')).toHaveText(
            '<strong>See you soon.</strong>',
        );
        await expect(
            visitorPage
                .getByTestId('coming-soon-message')
                .evaluate((element) => element.childElementCount),
        ).resolves.toBe(0);
        await expect(visitorPage.getByTestId('coming-soon-banner')).toHaveCount(
            0,
        );

        await page.goto('/');
        await expect(page.getByTestId('coming-soon-banner')).toBeVisible();
        await page.getByTestId('logout-button').click({ trial: true });
        await expect(page.getByTestId('coming-soon-banner')).toHaveCSS(
            'position',
            'fixed',
        );
        await expect(page.getByTestId('coming-soon-banner')).toHaveCSS(
            'bottom',
            '0px',
        );
        await expect(
            page.getByTestId('coming-soon-settings-link'),
        ).toHaveAttribute('href', urls.settings);
        await page.getByTestId('coming-soon-settings-link').click();
        await expect(page).toHaveURL(urls.settings);
        await page.getByTestId('coming-soon-enabled').setChecked(false);
        await page.getByTestId('coming-soon-save').click();
        await expect
            .poll(
                async () =>
                    (
                        await laravel.callFunction<{ enabled: boolean }>(
                            `${helper}::snapshot`,
                        )
                    ).enabled,
            )
            .toBe(false);

        await page.goto('/');
        await expect(page.getByTestId('coming-soon-banner')).toHaveCount(0);
        await visitorPage.goto(urls.page);
        await expect(visitorPage).toHaveURL(/\/$/);
    } finally {
        await laravel.callFunction(`${helper}::restore`, [original]);
        await visitor.close();
    }
});
