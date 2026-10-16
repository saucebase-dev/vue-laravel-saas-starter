<script setup lang="ts">
import { Link } from '@inertiajs/vue3';

import PostMeta from './PostMeta.vue';
import PostCategory from './PostCategory.vue';
import PostTags from './PostTags.vue';
defineProps<{
    post: Modules.Blog.Data.PostData;
}>();
</script>

<template>
    <article
        :data-testid="`post-card-${post.id}`"
        class="group hover:bg-card relative flex flex-col overflow-hidden rounded-2xl p-2 transition-all duration-200 hover:-translate-y-1 hover:shadow-xl"
    >
        <!-- Cover image -->
        <div class="aspect-video overflow-hidden rounded-xl">
            <img
                v-if="post.card_url"
                :src="post.card_url"
                :alt="post.title"
                loading="lazy"
                class="h-full w-full object-cover transition-transform duration-300 group-hover:scale-105"
            />
            <div
                v-else
                class="relative flex h-full w-full items-center justify-center bg-[linear-gradient(45deg,var(--primary),var(--secondary))] p-6"
            >
                <span
                    class="text-center text-lg leading-snug font-bold text-white/90 drop-shadow"
                >
                    {{ post.title }}
                </span>
            </div>
        </div>

        <div class="flex flex-1 flex-col gap-3 px-1 pt-4 pb-2">
            <div v-if="post.category">
                <PostCategory :category="post.category" />
            </div>

            <!-- The title's link stretches over the whole card, so the card
                 still opens the post without wrapping the category and tag
                 links inside another link. -->
            <h2
                :data-testid="`post-title-${post.id}`"
                class="text-foreground text-lg font-bold transition-colors group-hover:underline"
            >
                <Link :href="post.url" class="after:absolute after:inset-0">
                    {{ post.title }}
                </Link>
            </h2>

            <!-- Excerpt -->
            <p
                v-if="post.excerpt"
                class="text-muted-foreground line-clamp-3 text-sm"
            >
                {{ post.excerpt }}
            </p>

            <PostTags :tags="post.tags" />

            <!-- Author + publish date -->
            <div class="mt-auto pt-2">
                <PostMeta
                    :author="post.author"
                    :published-at="post.published_at"
                />
            </div>
        </div>
    </article>
</template>
