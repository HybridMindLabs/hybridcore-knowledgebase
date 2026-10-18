<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AdminLayout from '@/layouts/AdminLayout.vue';
import PageHeader from '@/components/UI/PageHeader.vue';
import Button from '@/components/UI/Button.vue';
import Badge from '@/components/UI/Badge.vue';
import FormModal from './FormModal.vue';
import { useLocale } from '@/composables/useLocale';
import {
    BookOpen, Plus, Edit, Trash2, Layers, Server, ShieldAlert,
    Wrench, HelpCircle, FileText, ArrowUp, ArrowDown,
    MessageSquare, Settings,
} from '@lucide/vue';

interface Category {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    icon: string;
    color: string;
    sort_order: number;
    is_active: boolean;
    articles_count: number;
}

const props = defineProps<{
    categories: Category[];
}>();

const { t } = useLocale();

const showModal = ref(false);
const selectedCategory = ref<Category | null>(null);

function openCreateModal() {
    selectedCategory.value = null;
    showModal.value = true;
}

function openEditModal(cat: Category) {
    selectedCategory.value = cat;
    showModal.value = true;
}

function deleteCategory(cat: Category) {
    if (confirm(`Are you sure you want to delete category "${cat.name}"? Articles in this category will also be deleted.`)) {
        router.delete(route('admin.knowledgebase.categories.destroy', { category: cat.id }));
    }
}

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
</script>

<template>
    <AdminLayout :title="t('ext.knowledgebase.admin_categories')">
        <Head :title="t('ext.knowledgebase.admin_categories')" />

        <PageHeader
            :title="t('ext.knowledgebase.admin_categories')"
            :icon="Layers"
            description="Organize articles into logical sections with custom icons, colors, and sort orders."
        >
            <template #actions>
                <Button @click="openCreateModal" variant="primary">
                    <Plus :size="16" />
                    <span>{{ t('ext.knowledgebase.new_category') }}</span>
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
                class="flex items-center gap-2 px-3.5 py-1.5 rounded-lg text-blue-400 bg-blue-500/10 border border-blue-500/20"
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

        <!-- Categories Table -->
        <div class="bg-[#111113] border border-zinc-800/80 rounded-2xl overflow-hidden shadow-sm">
            <table class="w-full text-left text-sm text-zinc-300">
                <thead class="bg-[#18181b]/70 border-b border-zinc-800 text-xs font-bold uppercase tracking-wider text-zinc-400">
                    <tr>
                        <th class="px-5 py-3.5 w-12 text-center">#</th>
                        <th class="px-5 py-3.5">Category</th>
                        <th class="px-5 py-3.5">Slug</th>
                        <th class="px-5 py-3.5">Articles</th>
                        <th class="px-5 py-3.5">Status</th>
                        <th class="px-5 py-3.5 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-800/60">
                    <tr v-for="cat in categories" :key="cat.id" class="hover:bg-zinc-800/20 transition-colors">
                        <td class="px-5 py-4 text-center font-mono text-xs text-zinc-500">
                            {{ cat.sort_order }}
                        </td>

                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-blue-500/10 text-blue-400 border border-blue-500/20">
                                    <component :is="getIcon(cat.icon)" :size="18" />
                                </div>
                                <div>
                                    <h4 class="font-bold text-zinc-100">{{ cat.name }}</h4>
                                    <p v-if="cat.description" class="text-xs text-zinc-400 line-clamp-1 mt-0.5">
                                        {{ cat.description }}
                                    </p>
                                </div>
                            </div>
                        </td>

                        <td class="px-5 py-4 font-mono text-xs text-zinc-400">
                            {{ cat.slug }}
                        </td>

                        <td class="px-5 py-4">
                            <span class="inline-flex px-2 py-0.5 rounded text-xs font-semibold bg-zinc-800 text-zinc-300">
                                {{ cat.articles_count }}
                            </span>
                        </td>

                        <td class="px-5 py-4">
                            <Badge :variant="cat.is_active ? 'success' : 'default'">
                                {{ cat.is_active ? 'Active' : 'Hidden' }}
                            </Badge>
                        </td>

                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-2">
                                <button
                                    @click="openEditModal(cat)"
                                    type="button"
                                    class="p-1.5 text-zinc-400 hover:text-zinc-100 hover:bg-zinc-800 rounded-lg transition"
                                    title="Edit Category"
                                >
                                    <Edit :size="16" />
                                </button>

                                <button
                                    @click="deleteCategory(cat)"
                                    type="button"
                                    class="p-1.5 text-red-400/80 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition"
                                    title="Delete Category"
                                >
                                    <Trash2 :size="16" />
                                </button>
                            </div>
                        </td>
                    </tr>

                    <tr v-if="categories.length === 0">
                        <td colspan="6" class="px-5 py-10 text-center text-zinc-500 text-sm">
                            No categories created yet. Click "New Category" to get started.
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <FormModal
            :show="showModal"
            :category="selectedCategory"
            @close="showModal = false"
        />
    </AdminLayout>
</template>
