<script setup lang="ts">
import { PageHero } from '@/components/ui/saucebase';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { Head, Link } from '@inertiajs/vue3';
import { trans } from 'laravel-vue-i18n';
import { computed } from 'vue';

import CategoryNav from '../components/CategoryNav.vue';
import PostCard from '../components/PostCard.vue';
import type { ListingHeading, PaginatedPosts } from '../types';

import IconNewspaper from '~icons/heroicons/newspaper';

const props = defineProps<{
    posts: PaginatedPosts;
    heading: ListingHeading | null;
    categories: Modules.Blog.Data.CategoryData[];
    activeCategory: string | null;
}>();

const title = computed(() => props.heading?.title ?? trans('Blog'));
const description = computed(
    () =>
        props.heading?.description ??
        trans('Tips, insights, and updates from our team.'),
);

const paginationClass =
    'bg-card text-card-foreground ring-border hover:bg-accent mt-8 cursor-pointer rounded-xl px-4 py-3 font-semibold shadow-lg ring-1 transition-all duration-200 ring-inset focus:outline-none focus-visible:outline-2 focus-visible:outline-offset-2';

const canonical = computed(() =>
    props.posts.current_page > 1
        ? `${props.posts.path}?page=${props.posts.current_page}`
        : props.posts.path,
);
</script>

<template>
    <SiteLayout
        :title="title"
        :description="description"
        :canonical="canonical"
    >
        <Head>
            <!-- The layout sets the same title; repeated so this head has one of its own. -->
            <title>{{ title }}</title>
            <link
                rel="alternate"
                type="application/rss+xml"
                :title="$t('Blog')"
                :href="route('blog.feed')"
            />
        </Head>

        <PageHero
            test-id="blog-hero"
            :title="title"
            :description="description"
            :icon="IconNewspaper"
            width="5xl"
        />

        <div class="w-full">
            <main class="mx-auto w-full max-w-5xl flex-1 px-6 py-16">
                <CategoryNav
                    :categories="categories"
                    :active="activeCategory"
                    :all-active="heading === null"
                />

                <!-- Empty state -->
                <div
                    v-if="posts.data.length === 0"
                    class="flex flex-col items-center justify-center py-20 text-center"
                >
                    <p class="text-muted-foreground">
                        {{ $t('No posts yet. Check back soon!') }}
                    </p>
                </div>

                <!-- Post grid -->
                <div v-else class="grid grid-cols-1 gap-8 lg:grid-cols-2">
                    <PostCard
                        v-for="post in posts.data"
                        :key="post.id"
                        :post="post"
                    />
                </div>

                <!-- Pagination -->
                <div
                    v-if="posts.last_page > 1"
                    class="mt-14 flex justify-center gap-2"
                >
                    <Link
                        v-if="posts.prev_page_url"
                        data-testid="pagination-previous"
                        :href="posts.prev_page_url"
                        :class="paginationClass"
                    >
                        {{ $t('← Newer posts') }}
                    </Link>
                    <Link
                        v-if="posts.next_page_url"
                        data-testid="pagination-next"
                        :href="posts.next_page_url"
                        :class="paginationClass"
                    >
                        {{ $t('Older posts →') }}
                    </Link>
                </div>
            </main>
        </div>
    </SiteLayout>
</template>
