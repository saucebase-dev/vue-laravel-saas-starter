import '@inertiajs/core';

declare module '@inertiajs/core' {
    interface PageProps {
        comingSoon?: { showBanner: boolean; settingsUrl: string | null };
    }
}
