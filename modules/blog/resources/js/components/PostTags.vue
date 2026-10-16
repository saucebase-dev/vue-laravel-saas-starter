<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    tags: Modules.Blog.Data.TagData[];
}>();
</script>

<template>
    <!-- `relative z-10` lifts the links above a card's stretched link, so they
         stay clickable on their own. -->
    <div
        v-if="tags.length > 0"
        class="relative z-10 flex flex-wrap items-center gap-1.5"
    >
        <Link
            v-for="tag in tags"
            :key="tag.slug"
            :href="route('blog.tag', tag.slug)"
            :data-testid="`post-tag-${tag.slug}`"
            class="bg-muted group-hover:bg-background text-muted-foreground hover:text-foreground rounded-full px-2.5 py-1 text-sm font-medium transition-colors"
        >
            #{{ tag.name }}
        </Link>
    </div>
</template>
