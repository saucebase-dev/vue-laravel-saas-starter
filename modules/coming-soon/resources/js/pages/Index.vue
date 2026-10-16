<script setup lang="ts">
import { Head } from '@inertiajs/vue3';

defineProps<{ title: string; message: string }>();
</script>

<template>
    <main
        class="bg-background text-foreground flex min-h-dvh items-center justify-center px-6 py-16"
        data-testid="coming-soon-page"
    >
        <Head :title="title" />
        <div class="flex max-w-xl flex-col gap-6 text-center">
            <h1
                class="text-4xl font-semibold tracking-tight sm:text-5xl"
                data-testid="coming-soon-title"
            >
                {{ title }}
            </h1>
            <p
                class="text-muted-foreground text-lg leading-relaxed whitespace-pre-line"
                data-testid="coming-soon-message"
            >
                {{ message }}
            </p>
        </div>
    </main>
</template>
