<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Breadcrumb from '@/components/UI/Breadcrumb.vue';
import Card from '@/components/UI/Card.vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale } from '@/composables/useLocale';
import {
    Search, BookOpen, Server, ShieldAlert, Wrench, HelpCircle,
    ChevronRight, Eye, Star, Pin, FileText, ArrowRight, X, Clock,
} from '@lucide/vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string;
    color: string;
    articles_count: number;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    views_count: number;
    is_pinned: boolean;
    is_featured: boolean;
    category?: {
        name: string;
        slug: string;
        icon: string;
        color: string;
    };
}

const props = defineProps<{
    categories: Category[];
    featuredArticles: Article[];
    popularArticles: Article[];
    totalArticles: number;
}>();

const { theme } = useTheme();
const { t } = useLocale();
const dark = computed(() => theme.value === 'dark');

// Map icon strings to Lucide components
const iconMap: Record<string, any> = {
    Server,
    ShieldAlert,
    Wrench,
    HelpCircle,
    BookOpen,
    FileText,
};

function getIconComponent(iconName: string) {
    return iconMap[iconName] || BookOpen;
}

// Color schemes for badges and category card accents
const colorMap: Record<string, { bg: string; text: string; border: string; glow: string }> = {
    blue: {
        bg: 'bg-blue-500/10 dark:bg-blue-500/15',
        text: 'text-blue-600 dark:text-blue-400',
        border: 'border-blue-500/20 dark:border-blue-500/30',
        glow: 'group-hover:border-blue-500/40',
    },
    red: {
        bg: 'bg-red-500/10 dark:bg-red-500/15',
        text: 'text-red-600 dark:text-red-400',
        border: 'border-red-500/20 dark:border-red-500/30',
        glow: 'group-hover:border-red-500/40',
    },
    amber: {
        bg: 'bg-amber-500/10 dark:bg-amber-500/15',
        text: 'text-amber-600 dark:text-amber-400',
        border: 'border-amber-500/20 dark:border-amber-500/30',
        glow: 'group-hover:border-amber-500/40',
    },
    emerald: {
        bg: 'bg-emerald-500/10 dark:bg-emerald-500/15',
        text: 'text-emerald-600 dark:text-emerald-400',
        border: 'border-emerald-500/20 dark:border-emerald-500/30',
        glow: 'group-hover:border-emerald-500/40',
    },
    purple: {
        bg: 'bg-purple-500/10 dark:bg-purple-500/15',
        text: 'text-purple-600 dark:text-purple-400',
        border: 'border-purple-500/20 dark:border-purple-500/30',
        glow: 'group-hover:border-purple-500/40',
    },
};

function getColorStyle(color: string) {
    return colorMap[color] || colorMap.blue;
}

// Live Search
const searchQuery = ref('');
const searchResults = ref<any[]>([]);
const isSearching = ref(false);
const searchOpen = ref(false);
let debounceTimer: ReturnType<typeof setTimeout> | null = null;

watch(searchQuery, (q) => {
    if (debounceTimer) clearTimeout(debounceTimer);
    if (!q || q.trim().length < 2) {
        searchResults.value = [];
        searchOpen.value = false;
        return;
    }

    debounceTimer = setTimeout(async () => {
        isSearching.value = true;
        try {
            const res = await fetch(route('knowledgebase.search', { q: q.trim() }));
            if (res.ok) {
                const data = await res.json();
                searchResults.value = data.results || [];
                searchOpen.value = true;
            }
        } catch {
            searchResults.value = [];
        } finally {
            isSearching.value = false;
        }
    }, 280);
});

function clearSearch() {
    searchQuery.value = '';
    searchResults.value = [];
    searchOpen.value = false;
}
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ t('ext.knowledgebase.title') }}</title>
            <meta name="description" :content="t('ext.knowledgebase.subtitle')" />
            <meta property="og:title" :content="t('ext.knowledgebase.title')" />
            <meta property="og:description" :content="t('ext.knowledgebase.subtitle')" />
            <meta property="og:type" content="website" />
        </Head>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <!-- Breadcrumb navigation -->
            <Breadcrumb :items="[{ label: t('navigation.home'), href: '/' }, { label: t('ext.knowledgebase.nav') }]" />

            <!-- Hero Section -->
            <div class="relative overflow-hidden rounded-3xl p-8 sm:p-12 mb-12 text-center border transition-all"
                :class="dark ? 'bg-gradient-to-b from-[#161a23] to-[#0e1117] border-white/10' : 'bg-gradient-to-b from-blue-50/50 to-white border-zinc-200/80 shadow-sm'">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-semibold mb-4 border"
                    :class="dark ? 'bg-blue-500/10 text-blue-400 border-blue-500/20' : 'bg-blue-50 text-blue-700 border-blue-200'">
                    <BookOpen :size="13" :stroke-width="2.5" />
                    <span>{{ t('ext.knowledgebase.title') }}</span>
                    <span class="w-1 h-1 rounded-full bg-blue-500"></span>
                    <span>{{ totalArticles }} {{ t('ext.knowledgebase.articles_count', { count: totalArticles }) }}</span>
                </div>

                <h1 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold tracking-tight mb-3"
                    :class="dark ? 'text-white' : 'text-zinc-900'">
                    {{ t('ext.knowledgebase.title') }}
                </h1>
                <p class="text-base sm:text-lg max-w-2xl mx-auto mb-8 font-normal"
                    :class="dark ? 'text-zinc-400' : 'text-zinc-600'">
                    {{ t('ext.knowledgebase.subtitle') }}
                </p>

                <!-- Search Input Container -->
                <div class="relative max-w-2xl mx-auto text-left">
                    <div class="relative flex items-center">
                        <Search :size="18" class="absolute left-4 pointer-events-none"
                            :class="dark ? 'text-zinc-500' : 'text-zinc-400'" />
                        <input
                            v-model="searchQuery"
                            type="search"
                            role="searchbox"
                            :aria-label="t('ext.knowledgebase.search_placeholder')"
                            :placeholder="t('ext.knowledgebase.search_placeholder')"
                            class="w-full pl-12 pr-10 py-3.5 rounded-2xl border text-sm sm:text-base font-medium transition focus:outline-none focus:ring-4 shadow-sm"
                            :class="dark
                                ? 'bg-zinc-900/90 text-zinc-100 border-zinc-700/80 focus:border-blue-500/80 focus:ring-blue-500/20'
                                : 'bg-white text-zinc-900 border-zinc-300 focus:border-blue-600 focus:ring-blue-500/15'"
                        />
                        <button v-if="searchQuery" @click="clearSearch" type="button" aria-label="Clear search"
                            class="absolute right-4 text-zinc-400 hover:text-zinc-200">
                            <X :size="16" />
                        </button>
                    </div>

                    <!-- Live search dropdown -->
                    <div v-if="searchOpen"
                        class="absolute left-0 right-0 mt-2 rounded-2xl border shadow-xl z-30 overflow-hidden backdrop-blur-md"
                        :class="dark ? 'bg-zinc-900/95 border-zinc-800' : 'bg-white/95 border-zinc-200'">
                        <div v-if="searchResults.length > 0" class="divide-y divide-zinc-800/40">
                            <Link
                                v-for="res in searchResults"
                                :key="res.id"
                                :href="res.url"
                                class="flex items-start justify-between gap-4 p-4 transition-colors"
                                :class="dark ? 'hover:bg-zinc-800/50' : 'hover:bg-zinc-50'"
                            >
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1">
                                        <span class="text-xs px-2 py-0.5 rounded font-medium"
                                            :class="dark ? 'bg-zinc-800 text-zinc-400' : 'bg-zinc-100 text-zinc-600'">
                                            {{ res.category_name }}
                                        </span>
                                        <h4 class="text-sm font-semibold truncate"
                                            :class="dark ? 'text-zinc-100' : 'text-zinc-900'">
                                            {{ res.title }}
                                        </h4>
                                    </div>
                                    <p v-if="res.excerpt" class="text-xs line-clamp-1"
                                        :class="dark ? 'text-zinc-400' : 'text-zinc-500'">
                                        {{ res.excerpt }}
                                    </p>
                                </div>
                                <ChevronRight :size="16" class="shrink-0 text-zinc-500 mt-1" />
                            </Link>
                        </div>
                        <div v-else class="p-6 text-center text-sm" :class="dark ? 'text-zinc-400' : 'text-zinc-500'">
                            {{ t('ext.knowledgebase.search_no_results') }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Categories Grid -->
            <div class="mb-14">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-xl sm:text-2xl font-bold tracking-tight" :class="dark ? 'text-white' : 'text-zinc-900'">
                            {{ t('ext.knowledgebase.all_categories') }}
                        </h2>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                    <Link
                        v-for="cat in categories"
                        :key="cat.id"
                        :href="route('knowledgebase.category', { category: cat.slug })"
                        class="group relative flex flex-col justify-between p-6 rounded-2xl border transition-all duration-200 hover:-translate-y-1 hover:shadow-lg"
                        :class="[
                            dark ? 'bg-[#12141a] border-white/5 hover:border-white/20' : 'bg-white border-zinc-200/90 hover:border-zinc-300 shadow-sm',
                            getColorStyle(cat.color).glow
                        ]"
                    >
                        <div>
                            <div class="w-12 h-12 rounded-xl flex items-center justify-center mb-4 border transition-transform duration-200 group-hover:scale-105"
                                :class="[getColorStyle(cat.color).bg, getColorStyle(cat.color).text, getColorStyle(cat.color).border]">
                                <component :is="getIconComponent(cat.icon)" :size="22" :stroke-width="2" />
                            </div>

                            <h3 class="text-lg font-bold mb-2 group-hover:text-blue-500 transition-colors"
                                :class="dark ? 'text-zinc-100' : 'text-zinc-900'">
                                {{ cat.name }}
                            </h3>

                            <p v-if="cat.description" class="text-xs sm:text-sm line-clamp-2 mb-4 leading-relaxed"
                                :class="dark ? 'text-zinc-400' : 'text-zinc-500'">
                                {{ cat.description }}
                            </p>
                        </div>

                        <div class="flex items-center justify-between text-xs font-semibold pt-3 border-t"
                            :class="dark ? 'border-zinc-800/80 text-zinc-500' : 'border-zinc-100 text-zinc-500'">
                            <span>{{ cat.articles_count }} {{ t('ext.knowledgebase.articles_count', { count: cat.articles_count }) }}</span>
                            <div class="flex items-center gap-1 text-blue-500 group-hover:translate-x-1 transition-transform">
                                <ArrowRight :size="14" />
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

            <!-- 2-Column: Featured Guides & Popular Guides -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <!-- Featured Guides (2 cols) -->
                <div class="lg:col-span-2">
                    <div class="flex items-center gap-2 mb-4">
                        <Star :size="18" class="text-amber-500" />
                        <h2 class="text-xl font-bold tracking-tight" :class="dark ? 'text-white' : 'text-zinc-900'">
                            {{ t('ext.knowledgebase.featured_articles') }}
                        </h2>
                    </div>

                    <div class="space-y-3">
                        <Link
                            v-for="article in featuredArticles"
                            :key="article.id"
                            :href="route('knowledgebase.article', { category: article.category?.slug ?? 'general', article: article.slug })"
                            class="group block p-5 rounded-2xl border transition-all duration-150 hover:shadow-md"
                            :class="dark ? 'bg-[#12141a] border-white/5 hover:border-blue-500/30' : 'bg-white border-zinc-200/90 hover:border-blue-400 shadow-sm'"
                        >
                            <div class="flex items-start justify-between gap-4">
                                <div class="min-w-0">
                                    <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                        <span v-if="article.category"
                                            class="text-[11px] font-bold px-2 py-0.5 rounded-full border"
                                            :class="[getColorStyle(article.category.color).bg, getColorStyle(article.category.color).text, getColorStyle(article.category.color).border]">
                                            {{ article.category.name }}
                                        </span>
                                        <span v-if="article.is_pinned"
                                            class="inline-flex items-center gap-1 text-[11px] font-bold px-2 py-0.5 rounded-full bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                            <Pin :size="10" />
                                            {{ t('ext.knowledgebase.pinned') }}
                                        </span>
                                    </div>

                                    <h3 class="text-base font-bold group-hover:text-blue-500 transition-colors mb-1"
                                        :class="dark ? 'text-zinc-100' : 'text-zinc-900'">
                                        {{ article.title }}
                                    </h3>

                                    <p v-if="article.excerpt" class="text-xs sm:text-sm line-clamp-2"
                                        :class="dark ? 'text-zinc-400' : 'text-zinc-600'">
                                        {{ article.excerpt }}
                                    </p>
                                </div>

                                <ChevronRight :size="18" class="text-zinc-500 group-hover:text-blue-500 group-hover:translate-x-1 transition-all shrink-0 mt-1" />
                            </div>
                        </Link>
                    </div>
                </div>

                <!-- Popular Articles Sidebar (1 col) -->
                <div>
                    <div class="flex items-center gap-2 mb-4">
                        <Eye :size="18" class="text-blue-500" />
                        <h2 class="text-xl font-bold tracking-tight" :class="dark ? 'text-white' : 'text-zinc-900'">
                            {{ t('ext.knowledgebase.popular_articles') }}
                        </h2>
                    </div>

                    <div class="rounded-2xl border p-5 space-y-4"
                        :class="dark ? 'bg-[#12141a] border-white/5' : 'bg-white border-zinc-200/90 shadow-sm'">
                        <Link
                            v-for="(art, idx) in popularArticles"
                            :key="art.id"
                            :href="route('knowledgebase.article', { category: art.category?.slug ?? 'general', article: art.slug })"
                            class="group flex items-start gap-3.5 pb-3.5 border-b last:border-b-0 last:pb-0"
                            :class="dark ? 'border-zinc-800/70' : 'border-zinc-100'"
                        >
                            <span class="w-6 h-6 rounded-lg flex items-center justify-center text-xs font-bold shrink-0 mt-0.5"
                                :class="dark ? 'bg-zinc-800 text-zinc-400' : 'bg-zinc-100 text-zinc-600'">
                                {{ idx + 1 }}
                            </span>

                            <div class="min-w-0">
                                <h4 class="text-sm font-semibold group-hover:text-blue-500 transition-colors line-clamp-2"
                                    :class="dark ? 'text-zinc-200' : 'text-zinc-800'">
                                    {{ art.title }}
                                </h4>
                                <div class="flex items-center gap-2 mt-1 text-xs text-zinc-500">
                                    <span v-if="art.category">{{ art.category.name }}</span>
                                    <span>•</span>
                                    <span>{{ art.views_count }} {{ t('ext.knowledgebase.views_count', { count: art.views_count }) }}</span>
                                </div>
                            </div>
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    </PublicLayout>
</template>
