<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PageHeader from '@/components/UI/PageHeader.vue';
import Pagination from '@/components/UI/Pagination.vue';
import Button from '@/components/UI/Button.vue';
import Badge from '@/components/UI/Badge.vue';
import { useLocale } from '@/composables/useLocale';
import {
    BookOpen, Plus, Search, Edit, Trash2, Pin, Star,
    Eye, ThumbsUp, Layers, MessageSquare, Settings, RotateCcw,
} from '@lucide/vue';

interface Category {
    id: number;
    name: string;
    color: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    status: 'draft' | 'published' | 'archived';
    is_pinned: boolean;
    is_featured: boolean;
    views_count: number;
    helpful_count: number;
    unhelpful_count: number;
    helpfulness_rating: number;
    category?: Category;
    created_at: string;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    articles: Paginated<Article>;
    categories: { id: number; name: string }[];
    filters: {
        search: string;
        category_id: string;
        status: string;
    };
}>();

const { t } = useLocale();

const search = ref(props.filters.search || '');
const categoryId = ref(props.filters.category_id || '');
const status = ref(props.filters.status || '');

function applyFilters() {
    router.get(
        route('admin.knowledgebase.articles.index'),
        {
            search: search.value,
            category_id: categoryId.value,
            status: status.value,
        },
        { preserveState: true, replace: true }
    );
}

function resetFilters() {
    search.value = '';
    categoryId.value = '';
    status.value = '';
    applyFilters();
}

function deleteArticle(article: Article) {
    if (confirm(`Are you sure you want to delete "${article.title}"?`)) {
        router.delete(route('admin.knowledgebase.articles.destroy', { article: article.id }));
    }
}
</script>

<template>
    <AdminLayout :title="t('ext.knowledgebase.admin_title')">
        <Head :title="t('ext.knowledgebase.admin_title')" />

        <PageHeader
            :title="t('ext.knowledgebase.admin_title')"
            :icon="BookOpen"
            description="Manage knowledgebase articles, categories, server guides, and player feedback."
        >
            <template #actions>
                <Link :href="route('admin.knowledgebase.articles.create')">
                    <Button variant="primary">
                        <Plus :size="16" />
                        <span>{{ t('ext.knowledgebase.new_article') }}</span>
                    </Button>
                </Link>
            </template>
        </PageHeader>

        <!-- Sub-Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-zinc-800 pb-3 mb-6 overflow-x-auto text-sm font-semibold">
            <Link
                :href="route('admin.knowledgebase.articles.index')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-blue-400 bg-blue-500/10 border border-blue-500/20"
            >
                <BookOpen :size="15" />
                <span>{{ t('ext.knowledgebase.admin_articles') }}</span>
            </Link>

            <Link
                :href="route('admin.knowledgebase.categories.index')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900 transition"
            >
                <Layers :size="15" />
                <span>{{ t('ext.knowledgebase.admin_categories') }}</span>
            </Link>

            <Link
                :href="route('admin.knowledgebase.feedback.index')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900 transition"
            >
                <MessageSquare :size="15" />
                <span>{{ t('ext.knowledgebase.admin_feedback') }}</span>
            </Link>

            <Link
                :href="route('admin.settings.extensions.knowledgebase')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900 transition ml-auto"
            >
                <Settings :size="15" />
                <span>{{ t('ext.knowledgebase.admin_settings') }}</span>
            </Link>
        </div>

        <!-- Filter Bar -->
        <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-4 gap-3">
                <div class="sm:col-span-2 relative">
                    <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 text-zinc-500 pointer-events-none" />
                    <input
                        v-model="search"
                        @keydown.enter="applyFilters"
                        type="text"
                        placeholder="Search by title or slug..."
                        class="w-full bg-[#18181b] border border-zinc-800 rounded-xl pl-10 pr-4 py-2 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div>
                    <select
                        v-model="categoryId"
                        @change="applyFilters"
                        class="w-full bg-[#18181b] border border-zinc-800 rounded-xl px-3 py-2 text-sm text-zinc-200 focus:outline-none focus:border-blue-500"
                    >
                        <option value="">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <select
                        v-model="status"
                        @change="applyFilters"
                        class="w-full bg-[#18181b] border border-zinc-800 rounded-xl px-3 py-2 text-sm text-zinc-200 focus:outline-none focus:border-blue-500"
                    >
                        <option value="">All Statuses</option>
                        <option value="published">Published</option>
                        <option value="draft">Draft</option>
                        <option value="archived">Archived</option>
                    </select>
                    <button
                        v-if="search || categoryId || status"
                        @click="resetFilters"
                        type="button"
                        class="p-2 text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800 rounded-xl transition shrink-0"
                        title="Reset Filters"
                    >
                        <RotateCcw :size="15" />
                    </button>
                </div>
            </div>
        </div>

        <!-- Articles Table -->
        <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm text-zinc-300">
                    <thead class="bg-[#18181b]/70 border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400">
                        <tr>
                            <th class="px-5 py-3.5">Article</th>
                            <th class="px-5 py-3.5">Category</th>
                            <th class="px-5 py-3.5">Status</th>
                            <th class="px-5 py-3.5">Views / Rating</th>
                            <th class="px-5 py-3.5 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-800/60">
                        <tr v-for="article in articles.data" :key="article.id" class="hover:bg-zinc-800/20 transition-colors">
                            <td class="px-5 py-4">
                                <div class="font-semibold text-zinc-100 flex items-center gap-2">
                                    <span v-if="article.is_pinned" title="Pinned" class="text-blue-400">
                                        <Pin :size="14" />
                                    </span>
                                    <span v-if="article.is_featured" title="Featured" class="text-amber-400">
                                        <Star :size="14" />
                                    </span>
                                    <span>{{ article.title }}</span>
                                </div>
                                <div class="text-xs text-zinc-500 font-mono mt-0.5">/kb/{{ article.category?.slug ?? 'cat' }}/{{ article.slug }}</div>
                            </td>

                            <td class="px-5 py-4">
                                <span v-if="article.category" class="inline-flex px-2.5 py-0.5 rounded-full text-xs font-semibold bg-zinc-800 text-zinc-300 border border-zinc-700/60">
                                    {{ article.category.name }}
                                </span>
                                <span v-else class="text-xs text-zinc-600">—</span>
                            </td>

                            <td class="px-5 py-4">
                                <Badge v-if="article.status === 'published'" variant="success">Published</Badge>
                                <Badge v-else-if="article.status === 'draft'" variant="warning">Draft</Badge>
                                <Badge v-else variant="default">Archived</Badge>
                            </td>

                            <td class="px-5 py-4">
                                <div class="flex items-center gap-4 text-xs font-medium">
                                    <span class="flex items-center gap-1 text-zinc-400">
                                        <Eye :size="13" />
                                        {{ article.views_count }}
                                    </span>
                                    <span class="flex items-center gap-1 text-emerald-400">
                                        <ThumbsUp :size="13" />
                                        {{ article.helpful_count }}
                                    </span>
                                </div>
                            </td>

                            <td class="px-5 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <Link
                                        :href="route('admin.knowledgebase.articles.edit', { article: article.id })"
                                        class="p-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 rounded-lg transition"
                                        title="Edit"
                                    >
                                        <Edit :size="16" />
                                    </Link>

                                    <button
                                        @click="deleteArticle(article)"
                                        type="button"
                                        class="p-1.5 text-red-400/80 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition"
                                        title="Delete"
                                    >
                                        <Trash2 :size="16" />
                                    </button>
                                </div>
                            </td>
                        </tr>

                        <tr v-if="articles.data.length === 0">
                            <td colspan="5" class="px-5 py-10 text-center text-zinc-500 text-sm">
                                No articles found matching your criteria.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-zinc-800">
                <Pagination :links="articles.links" />
            </div>
        </div>
    </AdminLayout>
</template>
