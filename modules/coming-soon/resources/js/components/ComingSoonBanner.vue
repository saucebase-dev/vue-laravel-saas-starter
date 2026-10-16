<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';

const page = usePage();
</script>

<template>
    <output
        v-if="page.props.comingSoon?.showBanner"
        class="fixed bottom-0 left-1/2 z-50 flex w-max max-w-[calc(100%-2rem)] -translate-x-1/2 flex-wrap items-center justify-center gap-x-4 gap-y-1 rounded-t-3xl bg-rose-500/20 px-6 py-4 text-center text-sm font-medium text-rose-950 shadow-[0_-12px_48px_-12px_rgb(0_0_0/0.4)] sm:px-8 dark:text-rose-100"
        :aria-label="$t('Coming Soon')"
        data-testid="coming-soon-banner"
    >
        <span>{{ $t('Coming Soon is active for non-admin users.') }}</span>
        <a
            v-if="page.props.comingSoon.settingsUrl"
            :href="page.props.comingSoon.settingsUrl"
            class="underline underline-offset-4"
            data-testid="coming-soon-settings-link"
        >
            {{ $t('Settings') }}
        </a>
    </output>
</template>
