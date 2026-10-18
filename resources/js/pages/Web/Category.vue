<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Breadcrumb from '@/components/UI/Breadcrumb.vue';
import Pagination from '@/components/UI/Pagination.vue';
import EmptyState from '@/components/UI/EmptyState.vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale } from '@/composables/useLocale';
import {
    Search, BookOpen, Server, ShieldAlert, Wrench, HelpCircle,
    ChevronRight, Pin, FileText, ArrowLeft,
} from '@lucide/vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string;
    color: string;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    views_count: number;
    is_pinned: boolean;
    server?: {
        id: number;
        name: string;
    } | null;
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
    category: Category;
    articles: Paginated<Article>;
    allCategories: Category[];
    filters: { search: string };
}>();

const { theme } = useTheme();
const { t } = useLocale();
const dark = computed(() => theme.value === 'dark');

const iconMap: Record<string, any> = {
    Server,
    ShieldAlert,
    Wrench,
    HelpCircle,
    BookOpen,
    FileText,
};

function getIcon(name: string) {
    return iconMap[name] || BookOpen;
}

const searchQuery = ref(props.filters.search || '');

function handleSearch() {
    router.get(
        route('knowledgebase.category', { category: props.category.slug }),
        { search: searchQuery.value },
        { preserveState: true, replace: true }
    );
}

function clearSearch() {
    searchQuery.value = '';
    handleSearch();
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ category.name }} — {{ t('ext.knowledgebase.title') }}</title>
            <meta name="description" :content="category.description || category.name" />
            <meta property="og:title" :content="`${category.name} — ${t('ext.knowledgebase.title')}`" />
            <meta property="og:description" :content="category.description || category.name" />
        </Head>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <Breadcrumb :items="[
                { label: t('navigation.home'), href: '/' },
                { label: t('ext.knowledgebase.nav'), href: route('knowledgebase.index') },
                { label: category.name }
            ]" />

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-8">
                <!-- Main Articles Column (3 cols) -->
                <div class="lg:col-span-3">
                    <!-- Category Header -->
                    <div class="p-6 sm:p-8 rounded-3xl border mb-8 transition-colors"
                        :class="dark ? 'bg-[#12141a] border-white/5' : 'bg-white border-zinc-200 shadow-sm'">
                        <div class="flex items-start gap-4">
                            <div class="w-14 h-14 rounded-2xl flex items-center justify-center shrink-0 bg-blue-500/10 text-blue-500 border border-blue-500/20">
                                <component :is="getIcon(category.icon)" :size="28" :stroke-width="2" />
                            </div>
                            <div class="flex-1 min-w-0">
                                <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight mb-2"
                                    :class="dark ? 'text-white' : 'text-zinc-900'">
                                    {{ category.name }}
                                </h1>
                                <p v-if="category.description" class="text-sm leading-relaxed"
                                    :class="dark ? 'text-zinc-400' : 'text-zinc-600'">
                                    {{ category.description }}
                                </p>
                            </div>
                        </div>

                        <!-- Filter search within category -->
                        <div class="mt-6 pt-6 border-t" :class="dark ? 'border-zinc-800' : 'border-zinc-100'">
                            <form @submit.prevent="handleSearch" class="relative">
                                <Search :size="16" class="absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none text-zinc-400" />
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    :placeholder="t('ext.knowledgebase.search_placeholder')"
                                    class="w-full pl-10 pr-20 py-2.5 rounded-xl border text-sm transition focus:outline-none focus:ring-2"
                                    :class="dark
                                        ? 'bg-zinc-900 text-zinc-100 border-zinc-800 focus:border-blue-500 focus:ring-blue-500/20'
                                        : 'bg-zinc-50 text-zinc-900 border-zinc-200 focus:border-blue-500 focus:ring-blue-500/20'"
                                />
                                <button
                                    v-if="searchQuery"
                                    @click="clearSearch"
                                    type="button"
                                    class="absolute right-3 top-1/2 -translate-y-1/2 text-xs font-semibold px-2 py-1 rounded hover:bg-zinc-500/10 text-zinc-400"
                                >
                                    Clear
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Articles List -->
                    <div v-if="articles.data.length > 0" class="space-y-4">
                        <Link
                            v-for="article in articles.data"
                            :key="article.id"
                            :href="route('knowledgebase.article', { category: category.slug, article: article.slug })"
                            class="group block p-6 rounded-2xl border transition-all duration-150 hover:-translate-y-0.5 hover:shadow-md"
                            :class="dark ? 'bg-[#12141a] border-white/5 hover:border-blue-500/30' : 'bg-white border-zinc-200/90 hover:border-blue-400 shadow-sm'"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2 mb-2 flex-wrap">
                                        <span v-if="article.is_pinned"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                            <Pin :size="10" />
                                            {{ t('ext.knowledgebase.pinned') }}
                                        </span>
                                        <span v-if="article.server"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                            <Server :size="10" />
                                            {{ article.server.name }}
                                        </span>
                                    </div>

                                    <h3 class="text-base sm:text-lg font-bold group-hover:text-blue-500 transition-colors mb-2"
                                        :class="dark ? 'text-zinc-100' : 'text-zinc-900'">
                                        {{ article.title }}
                                    </h3>

                                    <p v-if="article.excerpt" class="text-xs sm:text-sm line-clamp-2 leading-relaxed"
                                        :class="dark ? 'text-zinc-400' : 'text-zinc-600'">
                                        {{ article.excerpt }}
                                    </p>
                                </div>

                                <ChevronRight :size="20" class="text-zinc-500 group-hover:text-blue-500 group-hover:translate-x-1 transition-all shrink-0 mt-2" />
                            </div>
                        </Link>

                        <!-- Pagination -->
                        <div class="pt-6">
                            <Pagination :links="articles.links" />
                        </div>
                    </div>

                    <!-- Empty state -->
                    <div v-else class="p-12 text-center rounded-2xl border"
                        :class="dark ? 'bg-[#12141a] border-white/5' : 'bg-white border-zinc-200 shadow-sm'">
                        <BookOpen :size="36" class="mx-auto text-zinc-500 mb-3" />
                        <h3 class="text-base font-semibold mb-1" :class="dark ? 'text-zinc-300' : 'text-zinc-700'">
                            {{ t('ext.knowledgebase.empty_category') }}
                        </h3>
                        <Link :href="route('knowledgebase.index')" class="text-xs font-semibold text-blue-500 hover:underline inline-flex items-center gap-1 mt-2">
                            <ArrowLeft :size="12" />
                            {{ t('ext.knowledgebase.back_to_kb') }}
                        </Link>
                    </div>
                </div>

                <!-- Sidebar: Categories navigation (1 col) -->
                <div>
                    <div class="p-5 rounded-2xl border sticky top-20"
                        :class="dark ? 'bg-[#12141a] border-white/5' : 'bg-white border-zinc-200 shadow-sm'">
                        <h3 class="text-sm font-bold uppercase tracking-wider mb-4" :class="dark ? 'text-zinc-400' : 'text-zinc-500'">
                            {{ t('ext.knowledgebase.all_categories') }}
                        </h3>

                        <div class="space-y-1">
                            <Link
                                v-for="cat in allCategories"
                                :key="cat.id"
                                :href="route('knowledgebase.category', { category: cat.slug })"
                                class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-colors"
                                :class="cat.id === category.id
                                    ? (dark ? 'bg-blue-500/15 text-blue-400 font-bold' : 'bg-blue-50 text-blue-700 font-bold')
                                    : (dark ? 'text-zinc-400 hover:text-zinc-200 hover:bg-zinc-800/40' : 'text-zinc-600 hover:text-zinc-900 hover:bg-zinc-50')"
                            >
                                <component :is="getIcon(cat.icon)" :size="16" />
                                <span class="truncate">{{ cat.name }}</span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
