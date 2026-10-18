<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import { computed, onMounted, ref } from 'vue';
import PublicLayout from '@/layouts/PublicLayout.vue';
import Breadcrumb from '@/components/UI/Breadcrumb.vue';
import { useTheme } from '@/composables/useTheme';
import { useLocale } from '@/composables/useLocale';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import {
    BookOpen, Server, Calendar, Clock, Eye, ThumbsUp, ThumbsDown,
    Share2, Check, ArrowLeft, ArrowRight, FileText, ChevronRight,
    CornerDownRight, AlertCircle, Copy,
} from '@lucide/vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    icon: string;
    color: string;
}

interface Author {
    id: number;
    name: string;
    username: string | null;
    avatar: string | null;
}

interface ServerInfo {
    id: number;
    name: string;
    ip: string;
    port: number;
}

interface Article {
    id: number;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    views_count: number;
    helpful_count: number;
    unhelpful_count: number;
    helpfulness_rating: number;
    estimated_reading_time: number;
    published_at: string;
    author: Author | null;
    server: ServerInfo | null;
}

interface AdjacentArticle {
    id: number;
    title: string;
    slug: string;
}

const props = defineProps<{
    category: Category;
    article: Article;
    relatedArticles: { id: number; title: string; slug: string; excerpt: string | null; views_count: number }[];
    previousArticle: AdjacentArticle | null;
    nextArticle: AdjacentArticle | null;
}>();

const { theme } = useTheme();
const { t } = useLocale();
const dark = computed(() => theme.value === 'dark');

// Configure marked renderer for callouts and styling
const rawHtml = computed(() => {
    if (!props.article.content) return '';

    // Transform GitHub-style markdown alerts: > [!NOTE], > [!WARNING], > [!TIP]
    let content = props.article.content
        .replace(/>\s*\[!NOTE\]\s*(.*)/gi, '<div class="kb-callout kb-callout-note"><strong>Note:</strong> $1</div>')
        .replace(/>\s*\[!WARNING\]\s*(.*)/gi, '<div class="kb-callout kb-callout-warning"><strong>Warning:</strong> $1</div>')
        .replace(/>\s*\[!TIP\]\s*(.*)/gi, '<div class="kb-callout kb-callout-tip"><strong>Tip:</strong> $1</div>')
        .replace(/>\s*\[!CAUTION\]\s*(.*)/gi, '<div class="kb-callout kb-callout-danger"><strong>Caution:</strong> $1</div>');

    const parsed = marked.parse(content, { async: false, breaks: true, gfm: true }) as string;
    return DOMPurify.sanitize(parsed, {
        ADD_ATTR: ['target', 'rel'],
    });
});

// Table of Contents generator
interface TocItem {
    id: string;
    text: string;
    level: number;
}
const toc = ref<TocItem[]>([]);

onMounted(() => {
    // Generate IDs for headings and build TOC
    const articleContainer = document.querySelector('.kb-content');
    if (!articleContainer) return;

    const headings = articleContainer.querySelectorAll('h2, h3');
    const items: TocItem[] = [];

    headings.forEach((h, index) => {
        const text = h.textContent || '';
        const id = 'heading-' + index + '-' + text.toLowerCase().replace(/[^a-z0-9]+/g, '-').replace(/(^-|-$)/g, '');
        h.setAttribute('id', id);
        items.push({
            id,
            text,
            level: parseInt(h.tagName.substring(1), 10),
        });
    });

    toc.value = items;
});

// Feedback handling
const feedbackState = ref<'idle' | 'voted' | 'submitting'>('idle');
const feedbackHelpful = ref<boolean | null>(null);
const feedbackComment = ref('');
const feedbackMessage = ref('');
const showCommentInput = ref(false);

async function submitVote(isHelpful: boolean) {
    if (feedbackState.value === 'submitting') return;
    feedbackState.value = 'submitting';
    feedbackHelpful.value = isHelpful;

    try {
        const res = await fetch(route('knowledgebase.feedback', { article: props.article.id }), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                is_helpful: isHelpful,
                comment: feedbackComment.value || null,
            }),
        });

        const data = await res.json();
        feedbackMessage.value = data.message || t('ext.knowledgebase.feedback_recorded');
        feedbackState.value = 'voted';
        if (!isHelpful) {
            showCommentInput.value = true;
        }
    } catch {
        feedbackState.value = 'idle';
    }
}

async function submitComment() {
    if (!feedbackComment.value.trim()) return;

    try {
        await fetch(route('knowledgebase.feedback', { article: props.article.id }), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content || '',
            },
            body: JSON.stringify({
                is_helpful: feedbackHelpful.value ?? false,
                comment: feedbackComment.value,
            }),
        });
        showCommentInput.value = false;
        feedbackMessage.value = t('ext.knowledgebase.feedback_recorded');
    } catch {
        // Ignore
    }
}

// Copy article link
const copied = ref(false);
function copyArticleLink() {
    if (navigator.clipboard) {
        navigator.clipboard.writeText(window.location.href);
        copied.value = true;
        setTimeout(() => (copied.value = false), 2000);
    }
}

// Format date helper
function formatDate(iso: string) {
    if (!iso) return '';
    try {
        return new Date(iso).toLocaleDateString(undefined, {
            year: 'numeric',
            month: 'short',
            day: 'numeric',
        });
    } catch {
        return iso;
    }
}

// JSON-LD structured data schema
const schemaJson = computed(() => {
    return JSON.stringify({
        '@context': 'https://schema.org',
        '@type': 'Article',
        headline: props.article.title,
        description: props.article.excerpt,
        datePublished: props.article.published_at,
        author: {
            '@type': 'Person',
            name: props.article.author?.name || 'HybridCore',
        },
    });
});
</script>

<template>
    <PublicLayout>
        <Head>
            <title>{{ article.title }} — {{ category.name }}</title>
            <meta name="description" :content="article.excerpt || article.title" />
            <meta property="og:title" :content="article.title" />
            <meta property="og:description" :content="article.excerpt || article.title" />
            <meta property="og:type" content="article" />
            <!-- Structured Data JSON-LD for SEO -->
            <component is="script" type="application/ld+json" v-html="schemaJson" />
        </Head>

        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-8 md:py-12">
            <!-- Breadcrumb Navigation -->
            <Breadcrumb :items="[
                { label: t('navigation.home'), href: '/' },
                { label: t('ext.knowledgebase.nav'), href: route('knowledgebase.index') },
                { label: category.name, href: route('knowledgebase.category', { category: category.slug }) },
                { label: article.title }
            ]" />

            <div class="grid grid-cols-1 lg:grid-cols-4 gap-10 items-start">
                <!-- Main Article Content (3 cols) -->
                <article class="lg:col-span-3 min-w-0">
                    <header class="mb-8">
                        <div class="flex items-center gap-2 mb-3 flex-wrap">
                            <Link
                                :href="route('knowledgebase.category', { category: category.slug })"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full border transition-colors"
                                :class="dark ? 'bg-blue-500/10 text-blue-400 border-blue-500/20 hover:bg-blue-500/20' : 'bg-blue-50 text-blue-700 border-blue-200 hover:bg-blue-100'"
                            >
                                <BookOpen :size="12" />
                                <span>{{ category.name }}</span>
                            </Link>

                            <span v-if="article.server"
                                class="inline-flex items-center gap-1.5 text-xs font-bold px-3 py-1 rounded-full bg-emerald-500/10 text-emerald-500 border border-emerald-500/20">
                                <Server :size="12" />
                                <span>{{ article.server.name }}</span>
                            </span>
                        </div>

                        <h1 class="text-3xl sm:text-4xl font-extrabold tracking-tight mb-4"
                            :class="dark ? 'text-white' : 'text-zinc-900'">
                            {{ article.title }}
                        </h1>

                        <!-- Meta Info Row -->
                        <div class="flex items-center gap-4 text-xs sm:text-sm font-medium py-3 border-y flex-wrap"
                            :class="dark ? 'border-zinc-800 text-zinc-400' : 'border-zinc-200 text-zinc-600'">
                            <div v-if="article.author" class="flex items-center gap-2">
                                <img v-if="article.author.avatar" :src="article.author.avatar" :alt="article.author.name"
                                    class="w-5 h-5 rounded-full object-cover" />
                                <span>{{ article.author.name }}</span>
                            </div>

                            <div v-if="article.published_at" class="flex items-center gap-1.5">
                                <Calendar :size="14" class="text-zinc-500" />
                                <span>{{ formatDate(article.published_at) }}</span>
                            </div>

                            <div class="flex items-center gap-1.5">
                                <Clock :size="14" class="text-zinc-500" />
                                <span>{{ article.estimated_reading_time }} {{ t('ext.knowledgebase.read_time', { min: article.estimated_reading_time }) }}</span>
                            </div>

                            <div class="flex items-center gap-1.5 ml-auto">
                                <Eye :size="14" class="text-zinc-500" />
                                <span>{{ article.views_count }} {{ t('ext.knowledgebase.views_count', { count: article.views_count }) }}</span>
                            </div>

                            <button
                                @click="copyArticleLink"
                                type="button"
                                class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg border text-xs font-semibold transition"
                                :class="copied
                                    ? 'bg-emerald-500/10 text-emerald-400 border-emerald-500/20'
                                    : (dark ? 'bg-zinc-800 hover:bg-zinc-700 text-zinc-300 border-zinc-700' : 'bg-zinc-100 hover:bg-zinc-200 text-zinc-700 border-zinc-200')"
                            >
                                <component :is="copied ? Check : Share2" :size="13" />
                                <span>{{ copied ? 'Copied' : 'Share' }}</span>
                            </button>
                        </div>
                    </header>

                    <!-- Rendered HTML Content -->
                    <div
                        class="kb-content prose max-w-none mb-12 text-sm sm:text-base leading-relaxed"
                        :class="dark ? 'prose-invert prose-zinc' : 'prose-zinc'"
                        v-html="rawHtml"
                    />

                    <!-- Helpfulness Feedback Widget -->
                    <div class="rounded-2xl border p-6 sm:p-8 mb-10 text-center transition-colors"
                        :class="dark ? 'bg-[#12141a] border-white/10' : 'bg-zinc-50 border-zinc-200'">
                        <div v-if="feedbackState !== 'voted'">
                            <h3 class="text-base sm:text-lg font-bold mb-2" :class="dark ? 'text-zinc-100' : 'text-zinc-900'">
                                {{ t('ext.knowledgebase.was_this_helpful') }}
                            </h3>
                            <div class="flex items-center justify-center gap-4 mt-4">
                                <button
                                    @click="submitVote(true)"
                                    type="button"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition focus:outline-none focus:ring-2"
                                    :class="dark ? 'bg-emerald-500/15 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/25' : 'bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100'"
                                >
                                    <ThumbsUp :size="16" />
                                    <span>{{ t('ext.knowledgebase.yes') }}</span>
                                </button>
                                <button
                                    @click="submitVote(false)"
                                    type="button"
                                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl font-bold text-sm transition focus:outline-none focus:ring-2"
                                    :class="dark ? 'bg-red-500/15 text-red-400 border border-red-500/30 hover:bg-red-500/25' : 'bg-red-50 text-red-700 border border-red-200 hover:bg-red-100'"
                                >
                                    <ThumbsDown :size="16" />
                                    <span>{{ t('ext.knowledgebase.no') }}</span>
                                </button>
                            </div>
                        </div>

                        <div v-else class="space-y-4">
                            <div class="inline-flex items-center gap-2 text-sm font-semibold text-emerald-500">
                                <Check :size="18" />
                                <span>{{ feedbackMessage }}</span>
                            </div>

                            <div v-if="showCommentInput" class="max-w-md mx-auto pt-2">
                                <textarea
                                    v-model="feedbackComment"
                                    rows="3"
                                    :placeholder="t('ext.knowledgebase.feedback_optional_comment')"
                                    class="w-full rounded-xl border p-3 text-xs sm:text-sm transition focus:outline-none focus:ring-2"
                                    :class="dark ? 'bg-zinc-900 border-zinc-700 text-zinc-100' : 'bg-white border-zinc-300 text-zinc-900'"
                                />
                                <div class="flex justify-end mt-2">
                                    <button
                                        @click="submitComment"
                                        type="button"
                                        class="px-4 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 transition"
                                    >
                                        {{ t('ext.knowledgebase.submit_feedback') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Next & Previous Navigation Links -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-6 border-t"
                        :class="dark ? 'border-zinc-800' : 'border-zinc-200'">
                        <Link
                            v-if="previousArticle"
                            :href="route('knowledgebase.article', { category: category.slug, article: previousArticle.slug })"
                            class="p-4 rounded-xl border transition-all text-left group"
                            :class="dark ? 'bg-[#12141a] border-white/5 hover:border-zinc-700' : 'bg-white border-zinc-200 hover:border-zinc-300 shadow-sm'"
                        >
                            <span class="text-xs text-zinc-500 font-semibold flex items-center gap-1 mb-1">
                                <ArrowLeft :size="12" />
                                {{ t('ext.knowledgebase.previous_article') }}
                            </span>
                            <span class="text-sm font-bold group-hover:text-blue-500 transition-colors line-clamp-1"
                                :class="dark ? 'text-zinc-200' : 'text-zinc-800'">
                                {{ previousArticle.title }}
                            </span>
                        </Link>
                        <div v-else></div>

                        <Link
                            v-if="nextArticle"
                            :href="route('knowledgebase.article', { category: category.slug, article: nextArticle.slug })"
                            class="p-4 rounded-xl border transition-all text-right group"
                            :class="dark ? 'bg-[#12141a] border-white/5 hover:border-zinc-700' : 'bg-white border-zinc-200 hover:border-zinc-300 shadow-sm'"
                        >
                            <span class="text-xs text-zinc-500 font-semibold flex items-center justify-end gap-1 mb-1">
                                {{ t('ext.knowledgebase.next_article') }}
                                <ArrowRight :size="12" />
                            </span>
                            <span class="text-sm font-bold group-hover:text-blue-500 transition-colors line-clamp-1"
                                :class="dark ? 'text-zinc-200' : 'text-zinc-800'">
                                {{ nextArticle.title }}
                            </span>
                        </Link>
                    </div>
                </article>

                <!-- Right Sidebar: Table of Contents & Related (1 col) -->
                <aside class="space-y-6 sticky top-20">
                    <!-- Table of Contents -->
                    <div v-if="toc.length > 0" class="p-5 rounded-2xl border"
                        :class="dark ? 'bg-[#12141a] border-white/5' : 'bg-white border-zinc-200 shadow-sm'">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-3 text-zinc-500">
                            {{ t('ext.knowledgebase.table_of_contents') }}
                        </h3>
                        <nav class="space-y-1.5 max-h-[60vh] overflow-y-auto pr-1">
                            <a
                                v-for="heading in toc"
                                :key="heading.id"
                                :href="'#' + heading.id"
                                class="block text-xs transition-colors hover:text-blue-500 leading-snug"
                                :class="[
                                    heading.level === 3 ? 'pl-3' : 'font-medium',
                                    dark ? 'text-zinc-400' : 'text-zinc-600'
                                ]"
                            >
                                {{ heading.text }}
                            </a>
                        </nav>
                    </div>

                    <!-- Related Articles -->
                    <div v-if="relatedArticles.length > 0" class="p-5 rounded-2xl border"
                        :class="dark ? 'bg-[#12141a] border-white/5' : 'bg-white border-zinc-200 shadow-sm'">
                        <h3 class="text-xs font-bold uppercase tracking-wider mb-3 text-zinc-500">
                            {{ t('ext.knowledgebase.related_articles') }}
                        </h3>
                        <div class="space-y-3">
                            <Link
                                v-for="rel in relatedArticles"
                                :key="rel.id"
                                :href="route('knowledgebase.article', { category: category.slug, article: rel.slug })"
                                class="group block text-xs"
                            >
                                <span class="font-semibold group-hover:text-blue-500 transition-colors line-clamp-2"
                                    :class="dark ? 'text-zinc-300' : 'text-zinc-800'">
                                    {{ rel.title }}
                                </span>
                            </Link>
                        </div>
                    </div>
                </aside>
            </div>
        </div>
    </PublicLayout>
</template>

<style>
/* Callout alert boxes */
.kb-callout {
    padding: 1rem 1.25rem;
    margin: 1.25rem 0;
    border-radius: 0.875rem;
    border-left-width: 4px;
    font-size: 0.925rem;
    line-height: 1.6;
}
.kb-callout-note {
    background: rgba(59, 130, 246, 0.08);
    border-color: #3b82f6;
    color: #93c5fd;
}
.kb-callout-warning {
    background: rgba(245, 158, 11, 0.08);
    border-color: #f59e0b;
    color: #fcd34d;
}
.kb-callout-tip {
    background: rgba(16, 185, 129, 0.08);
    border-color: #10b981;
    color: #6ee7b7;
}
.kb-callout-danger {
    background: rgba(239, 68, 68, 0.08);
    border-color: #ef4444;
    color: #fca5a5;
}

/* Light theme overrides for callouts */
html:not(.dark) .kb-callout-note {
    background: #eff6ff;
    border-color: #3b82f6;
    color: #1e40af;
}
html:not(.dark) .kb-callout-warning {
    background: #fffbeb;
    border-color: #f59e0b;
    color: #92400e;
}
html:not(.dark) .kb-callout-tip {
    background: #ecfdf5;
    border-color: #10b981;
    color: #065f46;
}
html:not(.dark) .kb-callout-danger {
    background: #fef2f2;
    border-color: #ef4444;
    color: #991b1b;
}

/* Code blocks styling */
.kb-content pre {
    background: #090a0f !important;
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 0.75rem;
    padding: 1rem;
    overflow-x: auto;
}
</style>
