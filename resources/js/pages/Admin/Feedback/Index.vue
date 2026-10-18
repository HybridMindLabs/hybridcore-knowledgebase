<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PageHeader from '@/components/UI/PageHeader.vue';
import Pagination from '@/components/UI/Pagination.vue';
import Button from '@/components/UI/Button.vue';
import Badge from '@/components/UI/Badge.vue';
import { useLocale } from '@/composables/useLocale';
import {
    BookOpen, Layers, MessageSquare, Settings, ThumbsUp, ThumbsDown,
    Trash2, SearchX, Clock, User, Check, AlertCircle,
} from '@lucide/vue';

interface FeedbackItem {
    id: number;
    is_helpful: boolean;
    comment: string | null;
    created_at: string;
    article?: {
        id: number;
        title: string;
        slug: string;
        category?: {
            name: string;
            slug: string;
        };
    };
    user?: {
        name: string;
        username: string | null;
        avatar: string | null;
    } | null;
}

interface SearchMiss {
    id: number;
    query: string;
    hits_count: number;
    last_searched_at: string;
}

interface Paginated<T> {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    current_page: number;
    last_page: number;
    total: number;
}

const props = defineProps<{
    feedback: Paginated<FeedbackItem>;
    searchMisses: Paginated<SearchMiss>;
    filters: { helpful: string };
}>();

const { t } = useLocale();

function deleteFeedback(item: FeedbackItem) {
    if (confirm('Delete this feedback entry?')) {
        router.delete(route('admin.knowledgebase.feedback.destroy', { feedback: item.id }));
    }
}

function clearMiss(miss: SearchMiss) {
    router.delete(route('admin.knowledgebase.search-misses.clear', { miss: miss.id }));
}

function clearAllMisses() {
    if (confirm('Clear all recorded search misses?')) {
        router.post(route('admin.knowledgebase.search-misses.clear-all'));
    }
}

function formatDate(iso: string) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
        });
    } catch {
        return iso;
    }
}
</script>

<template>
    <AdminLayout :title="t('ext.knowledgebase.admin_feedback')">
        <Head :title="t('ext.knowledgebase.admin_feedback')" />

        <PageHeader
            :title="t('ext.knowledgebase.admin_feedback')"
            :icon="MessageSquare"
            description="Analyze helpfulness feedback and discover missing content from player search queries."
        />

        <!-- Sub-Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-zinc-800 pb-3 mb-6 overflow-x-auto text-sm font-semibold">
            <Link
                :href="route('admin.knowledgebase.articles.index')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900 transition"
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
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-blue-400 bg-blue-500/10 border border-blue-500/20"
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

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Feedback List -->
            <div class="lg:col-span-2 space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-zinc-100 flex items-center gap-2">
                        <ThumbsUp :size="17" class="text-blue-400" />
                        <span>Player Helpfulness Feedback</span>
                    </h3>
                </div>

                <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm">
                    <div v-if="feedback.data.length > 0" class="divide-y divide-zinc-800/60">
                        <div v-for="item in feedback.data" :key="item.id" class="p-4 sm:p-5 hover:bg-zinc-800/15 transition-colors">
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2.5 mb-1.5 flex-wrap">
                                        <Badge :variant="item.is_helpful ? 'success' : 'danger'">
                                            <component :is="item.is_helpful ? ThumbsUp : ThumbsDown" :size="11" />
                                            <span>{{ item.is_helpful ? 'Helpful' : 'Not Helpful' }}</span>
                                        </Badge>

                                        <Link
                                            v-if="item.article"
                                            :href="route('knowledgebase.article', { category: item.article.category?.slug ?? 'general', article: item.article.slug })"
                                            target="_blank"
                                            class="text-xs font-bold text-blue-400 hover:underline line-clamp-1"
                                        >
                                            {{ item.article.title }}
                                        </Link>
                                    </div>

                                    <p v-if="item.comment" class="text-sm text-zinc-200 mt-2 p-3 bg-[#18181b] rounded-xl border border-zinc-800/60">
                                        {{ item.comment }}
                                    </p>

                                    <div class="flex items-center gap-3 text-xs text-zinc-500 mt-2.5">
                                        <span v-if="item.user" class="flex items-center gap-1.5 font-medium text-zinc-400">
                                            <User :size="12" />
                                            <span>{{ item.user.name }}</span>
                                        </span>
                                        <span v-else>Guest User</span>

                                        <span>•</span>
                                        <span class="flex items-center gap-1">
                                            <Clock :size="12" />
                                            <span>{{ formatDate(item.created_at) }}</span>
                                        </span>
                                    </div>
                                </div>

                                <button
                                    @click="deleteFeedback(item)"
                                    type="button"
                                    class="p-1.5 text-zinc-500 hover:text-red-400 rounded-lg hover:bg-red-500/10 transition"
                                    title="Delete Feedback"
                                >
                                    <Trash2 :size="15" />
                                </button>
                            </div>
                        </div>
                    </div>

                    <div v-else class="p-10 text-center text-zinc-500 text-sm">
                        No feedback ratings submitted yet.
                    </div>

                    <div class="p-4 border-t border-zinc-800">
                        <Pagination :links="feedback.links" />
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Search Misses / Documentation Gaps -->
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-base font-bold text-zinc-100 flex items-center gap-2">
                        <SearchX :size="17" class="text-amber-400" />
                        <span>Search Misses</span>
                    </h3>

                    <button
                        v-if="searchMisses.data.length > 0"
                        @click="clearAllMisses"
                        type="button"
                        class="text-xs text-zinc-400 hover:text-red-400 font-semibold"
                    >
                        Clear All
                    </button>
                </div>

                <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl p-5 shadow-sm space-y-3">
                    <p class="text-xs text-zinc-400 leading-relaxed">
                        These are queries players searched for that yielded 0 results. Use this list to publish missing guides!
                    </p>

                    <div v-if="searchMisses.data.length > 0" class="divide-y divide-zinc-800/60 pt-2">
                        <div
                            v-for="miss in searchMisses.data"
                            :key="miss.id"
                            class="py-2.5 flex items-center justify-between text-xs"
                        >
                            <div class="min-w-0 pr-2">
                                <span class="font-mono font-bold text-zinc-200">"{{ miss.query }}"</span>
                                <div class="text-[11px] text-zinc-500 mt-0.5">{{ miss.hits_count }} searches</div>
                            </div>

                            <button
                                @click="clearMiss(miss)"
                                type="button"
                                class="text-zinc-500 hover:text-zinc-300 p-1"
                                title="Dismiss"
                            >
                                <Trash2 :size="13" />
                            </button>
                        </div>
                    </div>

                    <div v-else class="text-center py-6 text-xs text-zinc-500">
                        No search misses recorded. Everything searched was found!
                    </div>
                </div>
            </div>
        </div>
    </AdminLayout>
</template>
