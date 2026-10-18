<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale } from '@/composables/useLocale';
import { BookOpen, ChevronRight, Eye } from '@lucide/vue';

interface ArticleItem {
    id: number;
    title: string;
    slug: string;
    category_name: string;
    category_slug: string;
    category_color: string;
    views_count: number;
}

defineProps<{
    articles: ArticleItem[];
}>();

const { theme } = useTheme();
const { t } = useLocale();
const dark = computed(() => theme.value === 'dark');
</script>

<template>
    <div
        v-if="articles && articles.length > 0"
        class="rounded-2xl border p-5 mb-5 transition-colors"
        :class="dark ? 'bg-[#111113] border-zinc-800/80 shadow-sm' : 'bg-white border-zinc-200 shadow-sm'"
    >
        <div class="flex items-center justify-between mb-4 pb-3 border-b"
            :class="dark ? 'border-zinc-800/80' : 'border-zinc-100'">
            <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-lg flex items-center justify-center bg-blue-500/10 text-blue-500">
                    <BookOpen :size="15" />
                </div>
                <h3 class="text-sm font-bold tracking-tight" :class="dark ? 'text-zinc-100' : 'text-zinc-900'">
                    {{ t('ext.knowledgebase.title') }}
                </h3>
            </div>

            <Link
                :href="route('knowledgebase.index')"
                class="text-xs font-semibold text-blue-500 hover:text-blue-400 flex items-center gap-0.5"
            >
                <span>View all</span>
                <ChevronRight :size="12" />
            </Link>
        </div>

        <div class="space-y-3">
            <Link
                v-for="item in articles"
                :key="item.id"
                :href="route('knowledgebase.article', { category: item.category_slug ?? 'general', article: item.slug })"
                class="group block text-xs"
            >
                <div class="flex items-start justify-between gap-2">
                    <span class="font-medium group-hover:text-blue-500 transition-colors line-clamp-2"
                        :class="dark ? 'text-zinc-300' : 'text-zinc-700'">
                        {{ item.title }}
                    </span>
                    <span class="text-[10px] text-zinc-500 shrink-0 mt-0.5 flex items-center gap-1">
                        <Eye :size="11" />
                        {{ item.views_count }}
                    </span>
                </div>
            </Link>
        </div>
    </div>
</template>
