<script setup lang="ts">
import { Head, Link, useForm } from '@inertiajs/vue3';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PageHeader from '@/components/UI/PageHeader.vue';
import Button from '@/components/UI/Button.vue';
import Input from '@/components/UI/Input.vue';
import { useLocale } from '@/composables/useLocale';
import {
    Settings, BookOpen, Layers, MessageSquare, Save,
} from '@lucide/vue';

interface SettingsData {
    articles_per_page: number;
    enable_feedback: boolean;
    enable_search_tracking: boolean;
    show_toc: boolean;
    show_views_count: boolean;
    show_home_widget: boolean;
}

const props = defineProps<{
    settings: SettingsData;
}>();

const { t } = useLocale();

const form = useForm({
    articles_per_page: props.settings.articles_per_page ?? 12,
    enable_feedback: Boolean(props.settings.enable_feedback),
    enable_search_tracking: Boolean(props.settings.enable_search_tracking),
    show_toc: Boolean(props.settings.show_toc),
    show_views_count: Boolean(props.settings.show_views_count),
    show_home_widget: Boolean(props.settings.show_home_widget),
});

function submit() {
    form.patch(route('admin.settings.extensions.knowledgebase.update'));
}
</script>

<template>
    <AdminLayout :title="t('ext.knowledgebase.admin_settings')">
        <Head :title="t('ext.knowledgebase.admin_settings')" />

        <PageHeader
            :title="t('ext.knowledgebase.admin_settings')"
            :icon="Settings"
            description="Configure Knowledgebase behavior, feedback system, and widget visibility."
        >
            <template #actions>
                <Button @click="submit" variant="primary" :disabled="form.processing">
                    <Save :size="16" />
                    <span>{{ form.processing ? 'Saving...' : 'Save Settings' }}</span>
                </Button>
            </template>
        </PageHeader>

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
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-zinc-400 hover:text-zinc-200 hover:bg-zinc-900 transition"
            >
                <MessageSquare :size="15" />
                <span>{{ t('ext.knowledgebase.admin_feedback') }}</span>
            </Link>

            <Link
                :href="route('admin.settings.extensions.knowledgebase')"
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-blue-400 bg-blue-500/10 border border-blue-500/20 ml-auto"
            >
                <Settings :size="15" />
                <span>{{ t('ext.knowledgebase.admin_settings') }}</span>
            </Link>
        </div>

        <form @submit.prevent="submit" class="max-w-2xl">
            <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl p-6 space-y-6 shadow-sm">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1.5">
                        Articles Per Page (Category View)
                    </label>
                    <Input
                        type="number"
                        v-model="form.articles_per_page"
                        min="3"
                        max="50"
                        :error="form.errors.articles_per_page"
                        required
                    />
                    <p class="text-xs text-zinc-500 mt-1">Number of articles listed before pagination kicks in.</p>
                </div>

                <div class="space-y-4 pt-4 border-t border-zinc-800">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.enable_feedback"
                            class="mt-1 rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0"
                        />
                        <div>
                            <span class="text-sm font-semibold text-zinc-200 block">Enable Player Feedback</span>
                            <span class="text-xs text-zinc-500">Allows players to upvote/downvote guides and leave improvement comments.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.enable_search_tracking"
                            class="mt-1 rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0"
                        />
                        <div>
                            <span class="text-sm font-semibold text-zinc-200 block">Record Search Misses</span>
                            <span class="text-xs text-zinc-500">Keeps track of searches with 0 results so you can fill documentation gaps.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.show_toc"
                            class="mt-1 rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0"
                        />
                        <div>
                            <span class="text-sm font-semibold text-zinc-200 block">Show Table of Contents</span>
                            <span class="text-xs text-zinc-500">Automatically creates an interactive table of contents from article headings.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.show_views_count"
                            class="mt-1 rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0"
                        />
                        <div>
                            <span class="text-sm font-semibold text-zinc-200 block">Display View Counters</span>
                            <span class="text-xs text-zinc-500">Show number of views on public article pages and search results.</span>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <input
                            type="checkbox"
                            v-model="form.show_home_widget"
                            class="mt-1 rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0"
                        />
                        <div>
                            <span class="text-sm font-semibold text-zinc-200 block">Home Page Sidebar Widget</span>
                            <span class="text-xs text-zinc-500">Display popular Knowledgebase articles in the right column of the home page.</span>
                        </div>
                    </label>
                </div>

                <div class="pt-4 border-t border-zinc-800">
                    <Button type="submit" variant="primary" :disabled="form.processing">
                        <Save :size="16" />
                        <span>{{ form.processing ? 'Saving...' : 'Save Settings' }}</span>
                    </Button>
                </div>
            </div>
        </form>
    </AdminLayout>
</template>
