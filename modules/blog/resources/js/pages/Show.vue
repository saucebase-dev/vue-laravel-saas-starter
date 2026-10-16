<script setup lang="ts">
import {
    Breadcrumb,
    BreadcrumbItem,
    BreadcrumbLink,
    BreadcrumbList,
    BreadcrumbPage,
    BreadcrumbSeparator,
} from '@/components/ui/breadcrumb';
import SiteLayout from '@/layouts/SiteLayout.vue';
import { trans } from 'laravel-vue-i18n';
import { Head, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

import PostCard from '../components/PostCard.vue';
import PostMeta from '../components/PostMeta.vue';
import PostCategory from '../components/PostCategory.vue';
import PostTags from '../components/PostTags.vue';
const props = defineProps<{
    post: Modules.Blog.Data.PostData;
    related: Modules.Blog.Data.PostData[];
    /** An admin viewing a post that is not public yet. */
    preview: boolean;
}>();

// Inertia's Head renders text children but drops v-html. `<` is escaped so a
// title containing a closing script tag cannot end the JSON-LD block.
const toJsonLd = (data: object) =>
    JSON.stringify(data).replaceAll('<', String.raw`\u003c`);

const jsonLd = computed(() =>
    toJsonLd({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: props.post.title,
        description: props.post.excerpt ?? undefined,
        image: props.post.cover_url || undefined,
        datePublished: props.post.published_at ?? undefined,
        dateModified: props.post.updated_at ?? undefined,
        author: props.post.author
            ? { '@type': 'Person', name: props.post.author.name }
            : undefined,
        url: props.post.url,
    }),
);

// The same trail as the visible breadcrumbs, which search results can show in
// place of the raw URL.
const breadcrumbJsonLd = computed(() =>
    toJsonLd({
        '@context': 'https://schema.org',
        '@type': 'BreadcrumbList',
        itemListElement: [
            { name: trans('Blog'), item: route('blog.index') },
            ...(props.post.category
                ? [
                      {
                          name: props.post.category.name,
                          item: route(
                              'blog.category',
                              props.post.category.slug,
                          ),
                      },
                  ]
                : []),
            { name: props.post.title, item: props.post.url },
        ].map((crumb, index) => ({
            '@type': 'ListItem',
            position: index + 1,
            ...crumb,
        })),
    }),
);
</script>

<template>
    <SiteLayout
        :title="post.title"
        :description="post.excerpt ?? undefined"
        :image="post.cover_url || undefined"
        :canonical="post.url"
        type="article"
    >
        <Head>
            <meta v-if="preview" name="robots" content="noindex, nofollow" />
            <meta
                v-if="post.published_at"
                property="article:published_time"
                :content="post.published_at"
            />
            <meta
                v-if="post.author"
                property="article:author"
                :content="post.author.name"
            />
            <component :is="'script'" type="application/ld+json">{{
                jsonLd
            }}</component>
            <component :is="'script'" type="application/ld+json">{{
                breadcrumbJsonLd
            }}</component>
        </Head>
        <main class="mx-auto w-full max-w-3xl flex-1 px-6 py-28">
            <p
                v-if="preview"
                data-testid="post-preview-banner"
                role="status"
                class="mb-8 rounded-lg border border-amber-500/40 bg-amber-500/10 px-4 py-3 text-sm text-amber-900 dark:text-amber-200"
            >
                {{
                    $t(
                        'Preview: this post is not published, and only admins can see it.',
                    )
                }}
            </p>
            <Breadcrumb class="mb-8">
                <BreadcrumbList>
                    <BreadcrumbItem>
                        <BreadcrumbLink as-child>
                            <Link
                                :href="route('blog.index')"
                                data-testid="back-to-blog"
                            >
                                {{ $t('Blog') }}
                            </Link>
                        </BreadcrumbLink>
                    </BreadcrumbItem>
                    <template v-if="post.category">
                        <BreadcrumbSeparator />
                        <BreadcrumbItem>
                            <BreadcrumbLink as-child>
                                <Link
                                    :href="
                                        route(
                                            'blog.category',
                                            post.category.slug,
                                        )
                                    "
                                >
                                    {{ post.category.name }}
                                </Link>
                            </BreadcrumbLink>
                        </BreadcrumbItem>
                    </template>
                    <BreadcrumbSeparator />
                    <BreadcrumbItem class="min-w-0">
                        <BreadcrumbPage class="truncate">
                            {{ post.title }}
                        </BreadcrumbPage>
                    </BreadcrumbItem>
                </BreadcrumbList>
            </Breadcrumb>

            <!-- Title -->
            <h1
                data-testid="post-title"
                class="text-foreground mb-4 text-4xl leading-tight font-bold sm:text-5xl"
            >
                {{ post.title }}
            </h1>

            <!-- Meta: author, date -->
            <div class="mb-10">
                <PostMeta
                    :author="post.author"
                    :published-at="post.published_at"
                />
            </div>

            <!-- Cover image -->
            <div
                v-if="post.cover_url"
                class="bg-muted mb-12 overflow-hidden rounded-2xl"
            >
                <img
                    :src="post.cover_url"
                    :alt="post.title"
                    class="h-full w-full object-cover"
                />
            </div>

            <!-- Content -->
            <div
                data-testid="post-content"
                class="prose dark:prose-invert max-w-none leading-loose"
                v-html="post.content"
            />

            <!-- Filed under: after the post, once read, they point to more
                 on the same topics. -->
            <div
                v-if="post.category || post.tags.length > 0"
                data-testid="post-filed-under"
                class="border-border mt-12 space-y-3 border-t pt-8"
            >
                <div v-if="post.category" class="flex items-center gap-2">
                    <span class="text-muted-foreground text-sm font-medium"
                        >{{ $t('Category') }}:</span
                    >
                    <PostCategory :category="post.category" />
                </div>
                <div
                    v-if="post.tags.length > 0"
                    class="flex items-center gap-2"
                >
                    <span
                        class="text-muted-foreground shrink-0 text-sm font-medium"
                    >
                        {{ post.tags.length === 1 ? $t('Tag') : $t('Tags') }}:
                    </span>
                    <PostTags :tags="post.tags" />
                </div>
            </div>
        </main>

        <!-- You might also like -->
        <section v-if="related.length > 0">
            <div class="mx-auto mb-16 w-full max-w-6xl px-6 py-8">
                <h2
                    class="text-foreground mb-10 text-2xl font-bold tracking-tight"
                >
                    {{ $t('You might also like') }}
                </h2>

                <div
                    class="grid grid-cols-1 gap-8 sm:grid-cols-2 lg:grid-cols-3"
                >
                    <PostCard
                        v-for="item in related"
                        :key="item.id"
                        :post="item"
                    />
                </div>
            </div>
        </section>
    </SiteLayout>
</template>
