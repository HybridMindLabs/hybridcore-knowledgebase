<script setup lang="ts">
import { useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import Button from '@/components/UI/Button.vue';
import Input from '@/components/UI/Input.vue';
import { X, Check } from '@lucide/vue';

interface Category {
    id?: number;
    name: string;
    slug?: string;
    description: string | null;
    icon: string;
    color: string;
    sort_order: number;
    is_active: boolean;
}

const props = defineProps<{
    show: boolean;
    category: Category | null;
}>();

const emit = defineEmits<{ (e: 'close'): void }>();

const isEditing = computed(() => !!props.category?.id);

const form = useForm({
    id: props.category?.id ?? undefined,
    name: props.category?.name || '',
    slug: props.category?.slug || '',
    description: props.category?.description || '',
    icon: props.category?.icon || 'BookOpen',
    color: props.category?.color || 'blue',
    sort_order: props.category?.sort_order ?? 0,
    is_active: props.category ? Boolean(props.category.is_active) : true,
});

watch(() => props.category, (cat) => {
    form.id = cat?.id;
    form.name = cat?.name || '';
    form.slug = cat?.slug || '';
    form.description = cat?.description || '';
    form.icon = cat?.icon || 'BookOpen';
    form.color = cat?.color || 'blue';
    form.sort_order = cat?.sort_order ?? 0;
    form.is_active = cat ? Boolean(cat.is_active) : true;
});

const icons = [
    { label: 'Book', value: 'BookOpen' },
    { label: 'Server', value: 'Server' },
    { label: 'Shield / Rules', value: 'ShieldAlert' },
    { label: 'Wrench / Tools', value: 'Wrench' },
    { label: 'Help / Question', value: 'HelpCircle' },
    { label: 'Document', value: 'FileText' },
];

const colors = [
    { label: 'Blue', value: 'blue', bg: 'bg-blue-500' },
    { label: 'Red', value: 'red', bg: 'bg-red-500' },
    { label: 'Amber', value: 'amber', bg: 'bg-amber-500' },
    { label: 'Emerald', value: 'emerald', bg: 'bg-emerald-500' },
    { label: 'Purple', value: 'purple', bg: 'bg-purple-500' },
];

function submit() {
    if (isEditing.value && props.category?.id) {
        form.put(route('admin.knowledgebase.categories.update', { category: props.category.id }), {
            onSuccess: () => emit('close'),
        });
    } else {
        form.post(route('admin.knowledgebase.categories.store'), {
            onSuccess: () => emit('close'),
        });
    }
}
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/70 backdrop-blur-sm">
        <div class="relative w-full max-w-lg bg-[#111113] border border-zinc-800 rounded-2xl p-6 shadow-2xl">
            <div class="flex items-center justify-between pb-4 border-b border-zinc-800 mb-5">
                <h3 class="text-base font-bold text-zinc-100">
                    {{ isEditing ? 'Edit Category' : 'Create Category' }}
                </h3>
                <button @click="$emit('close')" type="button" class="text-zinc-500 hover:text-zinc-300">
                    <X :size="18" />
                </button>
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">
                        Category Name <span class="text-red-400">*</span>
                    </label>
                    <Input v-model="form.name" placeholder="e.g. Game Servers & Rules" :error="form.errors.name" required />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">
                        URL Slug
                    </label>
                    <Input v-model="form.slug" placeholder="e.g. server-rules" :error="form.errors.slug" />
                </div>

                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">
                        Description
                    </label>
                    <textarea
                        v-model="form.description"
                        rows="2"
                        placeholder="Brief summary of what this category covers..."
                        class="w-full bg-[#18181b] border border-zinc-800 rounded-xl p-3 text-sm text-zinc-100 placeholder-zinc-500 focus:outline-none focus:border-blue-500"
                    />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Icon</label>
                        <select
                            v-model="form.icon"
                            class="w-full bg-[#18181b] border border-zinc-800 rounded-xl px-3 py-2 text-sm text-zinc-100 focus:outline-none focus:border-blue-500"
                        >
                            <option v-for="ico in icons" :key="ico.value" :value="ico.value">{{ ico.label }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Color Theme</label>
                        <div class="flex items-center gap-2 pt-1.5">
                            <button
                                v-for="col in colors"
                                :key="col.value"
                                type="button"
                                @click="form.color = col.value"
                                class="w-6 h-6 rounded-full flex items-center justify-center transition-transform"
                                :class="[col.bg, form.color === col.value ? 'ring-2 ring-white scale-110' : 'opacity-70 hover:opacity-100']"
                            >
                                <Check v-if="form.color === col.value" :size="12" class="text-white" />
                            </button>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 pt-2">
                    <div>
                        <label class="block text-xs font-bold uppercase tracking-wider text-zinc-400 mb-1">Sort Order</label>
                        <Input type="number" v-model="form.sort_order" />
                    </div>

                    <div class="flex items-center pt-5">
                        <label class="flex items-center gap-2 cursor-pointer text-sm text-zinc-300">
                            <input type="checkbox" v-model="form.is_active" class="rounded bg-zinc-800 border-zinc-700 text-blue-600 focus:ring-0" />
                            <span>Active / Visible</span>
                        </label>
                    </div>
                </div>

                <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-800">
                    <Button @click="$emit('close')" variant="ghost" type="button">Cancel</Button>
                    <Button variant="primary" type="submit" :disabled="form.processing">
                        {{ isEditing ? 'Save Changes' : 'Create Category' }}
                    </Button>
                </div>
            </form>
        </div>
    </div>
</template>
