<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PageHeader from '@/components/UI/PageHeader.vue';
import Button from '@/components/UI/Button.vue';
import Input from '@/components/UI/Input.vue';
import { useLocale } from '@/composables/useLocale';
import { marked } from 'marked';
import DOMPurify from 'dompurify';
import {
    BookOpen, ArrowLeft, Save, Eye, Edit3, Server,
    Pin, Star, Sparkles, HelpCircle,
} from '@lucide/vue';

interface Category {
    id: number;
    name: string;
}

interface ServerOption {
    id: number;
    name: string;
}

interface Article {
    id?: number;
    category_id: number;
    server_id: number | null;
    title: string;
    slug: string;
    excerpt: string | null;
    content: string;
    status: 'draft' | 'published' | 'archived';
    is_pinned: boolean;
    is_featured: boolean;
    sort_order: number;
    meta_title: string | null;
    meta_description: string | null;
}

const props = defineProps<{
    article: Article | null;
    categories: Category[];
    servers: ServerOption[];
}>();

const { t } = useLocale();
const isEditing = computed(() => !!props.article?.id);

const form = useForm({
    category_id: props.article?.category_id || (props.categories[0]?.id ?? ''),
    server_id: props.article?.server_id ?? '',
    title: props.article?.title || '',
    slug: props.article?.slug || '',
    excerpt: props.article?.excerpt || '',
    content: props.article?.content || '',
    status: props.article?.status || 'published',
    is_pinned: Boolean(props.article?.is_pinned),
    is_featured: Boolean(props.article?.is_featured),
    sort_order: props.article?.sort_order ?? 0,
    meta_title: props.article?.meta_title || '',
    meta_description: props.article?.meta_description || '',
});

// Auto-slug when title changes (if not editing or slug is empty)
watch(() => form.title, (newTitle) => {
    if (!isEditing.value || !form.slug) {
        form.slug = newTitle
            .toLowerCase()
            .trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/[\s-]+/g, '-');
    }
});

// Editor tab: edit vs preview
const activeTab = ref<'edit' | 'preview'>('edit');
const previewHtml = computed(() => {
    if (!form.content) return '<p class="text-zinc-500 italic">No content to preview.</p>';
    const parsed = marked.parse(form.content, { async: false, breaks: true }) as string;
    return DOMPurify.sanitize(parsed);
});

function submit() {
    if (isEditing.value && props.article?.id) {
        form.put(route('admin.knowledgebase.articles.update', { article: props.article.id }));
    } else {
        form.post(route('admin.knowledgebase.articles.store'));
    }
}
</script>

<template>
    <AdminLayout :title="isEditing ? t('ext.knowledgebase.edit_article') : t('ext.knowledgebase.new_article')">
        <Head :title="isEditing ? t('ext.knowledgebase.edit_article') : t('ext.knowledgebase.new_article')" />

        <PageHeader
            :title="isEditing ? t('ext.knowledgebase.edit_article') : t('ext.knowledgebase.new_article')"
            :icon="BookOpen"
            :description="isEditing ? `Editing: ${props.article?.title}` : 'Publish a new guide or documentation entry.'"
        >
            <template #actions>
                <Link :href="route('admin.knowledgebase.articles.index')">
                    <Button variant="ghost">
                        <ArrowLeft :size="16" />
                        <span>Back to Articles</span>
                    </Button>
                </Link>
                <Button @click="submit" variant="primary" :disabled="form.processing">
                    <Save :size="16" />
                    <span>{{ form.processing ? 'Saving...' : 'Save Article' }}</span>
                </Button>
            </template>
        </PageHeader>

        <form @submit.prevent="submit" class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left 2 Cols: Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Title & Slug -->
                <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl p-6 space-y-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">
                            Article Title <span class="text-red-400">*</span>
                        </label>
                        <Input
                            v-model="form.title"
                            placeholder="e.g. How to report a player or server cheater"
                            :error="form.errors.title"
                            required
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">
                            URL Slug
                        </label>
                        <Input
                            v-model="form.slug"
                            placeholder="e.g. how-to-report-a-player"
                            :error="form.errors.slug"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">
                            Summary / Excerpt
                        </label>
                        <textarea
                            v-model="form.excerpt"
                            rows="2"
                            placeholder="Brief description for search previews and cards..."
                            class="w-full bg-[#18181b] border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-blue-500"
                        />
                    </div>
                </div>

                <!-- Markdown Content Editor with Preview -->
                <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm">
                    <div class="flex items-center justify-between border-b border-zinc-800 px-5 py-3 bg-[#18181b]/50">
                        <div class="flex items-center gap-1">
                            <button
                                type="button"
                                @click="activeTab = 'edit'"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                :class="activeTab === 'edit' ? 'bg-zinc-800 text-zinc-100' : 'text-zinc-400 hover:text-zinc-200'"
                            >
                                <Edit3 :size="13" />
                                <span>Markdown</span>
                            </button>
                            <button
                                type="button"
                                @click="activeTab = 'preview'"
                                class="flex items-center gap-1.5 px-3 py-1.5 rounded-lg text-xs font-semibold transition"
                                :class="activeTab === 'preview' ? 'bg-zinc-800 text-zinc-100' : 'text-zinc-400 hover:text-zinc-200'"
                            >
                                <Eye :size="13" />
                                <span>Preview</span>
                            </button>
                        </div>
                        <span class="text-xs text-zinc-500">Supports GitHub Markdown & Callouts</span>
                    </div>

                    <div class="p-5">
                        <textarea
                            v-show="activeTab === 'edit'"
                            v-model="form.content"
                            rows="18"
                            placeholder="Write your article content in Markdown here...

## Section Title
Explain your steps clearly.

> [!NOTE] This is a note callout.
> [!WARNING] This is a warning callout.

```bash
bind f &quot;say_team Drop me AWP&quot;
```"
                            class="w-full font-mono text-xs sm:text-sm bg-[#18181b] border border-zinc-800 rounded-xl p-4 text-zinc-100 placeholder-zinc-600 focus:outline-none focus:border-blue-500 leading-relaxed"
                            required
                        />

                        <div
                            v-show="activeTab === 'preview'"
                            class="prose prose-invert max-w-none min-h-[300px] p-4 bg-[#18181b]/40 rounded-xl border border-zinc-800/40 text-sm"
                            v-html="previewHtml"
                        />
                    </div>
                </div>

                <!-- SEO Meta Information -->
                <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl p-6 space-y-4">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-zinc-400">SEO & Metadata</h3>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Custom Meta Title</label>
                        <Input v-model="form.meta_title" placeholder="Leave empty to use article title" />
                    </div>
                    <div>
                        <label class="block text-xs text-zinc-400 mb-1">Meta Description</label>
                        <textarea
                            v-model="form.meta_description"
                            rows="2"
                            placeholder="Leave empty to use summary"
                            class="w-full bg-[#18181b] border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-blue-500"
                        />
                    </div>
                </div>
            </div>

            <!-- Right 1 Col: Publishing Settings & Taxonomies -->
            <div class="space-y-6">
                <!-- Status & Publish Panel -->
                <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl p-6 space-y-4">
                    <h3 class="text-xs font-bold uppercase tracking-wider text-zinc-400 mb-3">Publication</h3>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-1.5">Status</label>
                        <select
                            v-model="form.status"
                            class="w-full bg-[#18181b] border border-zinc-800 rounded-xl px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-blue-500"
                        >
                            <option value="published">Published</option>
                            <option value="draft">Draft (Hidden)</option>
                            <option value="archived">Archived</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-1.5">Category <span class="text-red-400">*</span></label>
                        <select
                            v-model="form.category_id"
                            class="w-full bg-[#18181b] border border-zinc-800 rounded-xl px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-blue-500"
                            required
                        >
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-1.5">Attach to Game Server (Optional)</label>
                        <select
                            v-model="form.server_id"
                            class="w-full bg-[#18181b] border border-zinc-800 rounded-xl px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-blue-500"
                        >
                            <option value="">None (Global Guide)</option>
                            <option v-for="srv in servers" :key="srv.id" :value="srv.id">{{ srv.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs text-zinc-400 mb-1.5">Sort Order</label>
                        <Input type="number" v-model="form.sort_order" />
                    </div>

                    <div class="pt-2 space-y-2 border-t border-zinc-800">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300">
                            <input type="checkbox" v-model="form.is_pinned" class="rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0" />
                            <span>Pin to top of category</span>
                        </label>
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300">
                            <input type="checkbox" v-model="form.is_featured" class="rounded bg-zinc-800 border-zinc-700 text-amber-600 focus:ring-0" />
                            <span>Feature on Knowledgebase home</span>
                        </label>
                    </div>

                    <div class="pt-4">
                        <Button @click="submit" variant="primary" class="w-full" :disabled="form.processing">
                            <Save :size="16" />
                            <span>{{ isEditing ? 'Update Article' : 'Publish Article' }}</span>
                        </Button>
                    </div>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
