<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

defineProps<{
    category: Modules.Blog.Data.CategoryData;
}>();
</script>

<template>
    <!-- `relative z-10` lifts the link above a card's stretched link, so it
         stays clickable on its own. -->
    <Link
        :href="route('blog.category', category.slug)"
        data-testid="post-category-link"
        class="bg-secondary/80 text-secondary-foreground/80 hover:bg-secondary relative z-10 rounded-full px-2.5 py-1 text-xs font-semibold transition-colors"
    >
        {{ category.name }}
    </Link>
</template>
