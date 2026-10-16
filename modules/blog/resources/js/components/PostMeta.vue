<script setup lang="ts">
import { Avatar, AvatarFallback, AvatarImage } from '@/components/ui/avatar';
import { useLocalization } from '@saucebase/vue/composables/useLocalization';
import { formatDate } from '@saucebase/core/lib/dates';

defineProps<{
    author: Modules.Blog.Data.AuthorData | null;
    publishedAt: string | null;
}>();

const { language } = useLocalization();
</script>

<template>
    <div class="flex items-center gap-2">
        <Avatar v-if="author" class="size-6">
            <AvatarImage :src="author.avatar_url ?? ''" :alt="author.name" />
            <AvatarFallback
                class="bg-primary/20 text-primary text-xs font-bold"
            >
                {{ author.name.charAt(0).toUpperCase() }}
            </AvatarFallback>
        </Avatar>
        <div class="flex items-center gap-3 text-sm">
            <span v-if="author" class="text-foreground font-semibold">{{
                author.name
            }}</span>
            <time v-if="publishedAt" class="text-muted-foreground">{{
                formatDate(publishedAt, language)
            }}</time>
        </div>
    </div>
</template>
