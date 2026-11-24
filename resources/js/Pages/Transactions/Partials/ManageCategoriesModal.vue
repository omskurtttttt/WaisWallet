<script setup>
import { ref, computed, watch } from 'vue';
import { useForm, router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Badge from '@/Components/UI/Badge.vue';
import { useIcons } from '@/Composables/useIcons';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    categories: {
        type: Array,
        default: () => [],
    },
});

const emit = defineEmits(['close']);

const { getIconSvg } = useIcons();

const activeFilter = ref('all'); // all, expense, income
const isCreating = ref(false);
const editingCategory = ref(null);
const errorMessage = ref('');

const filteredCategories = computed(() => {
    if (activeFilter.value === 'all') return props.categories;
    return props.categories.filter(c => c.type === activeFilter.value);
});

const colors = [
    { name: 'Purple', value: '#8b5cf6' },
    { name: 'Blue', value: '#3b82f6' },
    { name: 'Indigo', value: '#6366f1' },
    { name: 'Pink', value: '#ec4899' },
    { name: 'Rose', value: '#f43f5e' },
    { name: 'Orange', value: '#f97316' },
    { name: 'Teal', value: '#14b8a6' },
    { name: 'Emerald', value: '#10b981' },
    { name: 'Slate', value: '#64748b' },
    { name: 'Zinc', value: '#71717a' },
];

const icons = [
    { name: 'Tag', value: 'tag' },
    { name: 'Food', value: 'coffee' },
    { name: 'Shopping', value: 'shopping-cart' },
    { name: 'Transport', value: 'car' },
    { name: 'Home', value: 'home' },
    { name: 'Bills', value: 'zap' },
    { name: 'Fun', value: 'film' },
    { name: 'Health', value: 'heart-pulse' },
    { name: 'Education', value: 'graduation-cap' },
    { name: 'Store', value: 'shopping-bag' },
    { name: 'Travel', value: 'plane' },
    { name: 'Work', value: 'briefcase' },
    { name: 'Computer', value: 'monitor' },
    { name: 'Gifts', value: 'gift' },
    { name: 'Investment', value: 'trending-up' },
];

const emojiSuggestions = [
    '🍔', '🛒', '🚗', '🏠', '🎮', '🏥', '📚', '🛍', '✈', '💸',
    '💼', '🧑‍💻', '📈', '🎁', '💰', '🪙', '🥤', '🍿', '🎨', '📱'
];

// Create Form
const createForm = useForm({
    name: '',
    type: 'expense',
    icon: 'tag',
    color: '#3b82f6',
});

// Edit Form
const editForm = useForm({
    name: '',
    type: 'expense',
    icon: 'tag',
    color: '#3b82f6',
});

const startCreate = () => {
    editingCategory.value = null;
    errorMessage.value = '';
    createForm.reset();
    createForm.type = activeFilter.value === 'income' ? 'income' : 'expense';
    isCreating.value = true;
};

const startEdit = (cat) => {
    isCreating.value = false;
    errorMessage.value = '';
    editingCategory.value = cat;
    editForm.name = cat.name;
    editForm.type = cat.type;
    editForm.icon = cat.icon;
    editForm.color = cat.color;
};

const cancelAction = () => {
    isCreating.value = false;
    editingCategory.value = null;
    errorMessage.value = '';
    createForm.reset();
    editForm.reset();
};

const submitCreate = () => {
    errorMessage.value = '';
    createForm.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            isCreating.value = false;
            createForm.reset();
        },
        onError: (errs) => {
            if (errs.error) errorMessage.value = errs.error;
        }
    });
};

const submitEdit = () => {
    if (!editingCategory.value) return;
    errorMessage.value = '';
    editForm.put(route('categories.update', editingCategory.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            editingCategory.value = null;
            editForm.reset();
        },
        onError: (errs) => {
            if (errs.error) errorMessage.value = errs.error;
        }
    });
};

const deleteCategory = (cat) => {
    if (!confirm(`Are you sure you want to delete category "${cat.name}"?`)) return;
    errorMessage.value = '';
    router.delete(route('categories.destroy', cat.id), {
        preserveScroll: true,
        onError: (errs) => {
            if (errs.error) errorMessage.value = errs.error;
        }
    });
};

const addEmojiToCreate = (emoji) => {
    let cleaned = createForm.name.trim().replace(/^\p{Emoji}\s*/u, '');
    createForm.name = `${emoji} ${cleaned}`;
};

const addEmojiToEdit = (emoji) => {
    let cleaned = editForm.name.trim().replace(/^\p{Emoji}\s*/u, '');
    editForm.name = `${emoji} ${cleaned}`;
};

const close = () => {
    cancelAction();
    emit('close');
};

watch(() => props.show, (val) => {
    if (!val) cancelAction();
});
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="p-6">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h2 class="text-xl font-bold text-foreground">Manage Categories</h2>
                    <p class="text-xs text-muted-foreground mt-0.5">Customize, edit, and organize your transaction categories</p>
                </div>
                <PrimaryButton v-if="!isCreating && !editingCategory" @click="startCreate" class="text-xs py-2 px-3">
                    + Add New
                </PrimaryButton>
            </div>

            <!-- Error Banner -->
            <div v-if="errorMessage" class="mb-4 p-3 rounded-xl bg-destructive/10 border border-destructive/20 text-destructive text-sm flex items-start gap-2">
                <svg class="h-5 w-5 shrink-0 mt-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                <span>{{ errorMessage }}</span>
            </div>

            <!-- Create Category Form Drawer -->
            <div v-if="isCreating" class="mb-6 p-4 rounded-xl border border-border bg-muted/20 animate-fade-in space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-foreground">Create New Category</h3>
                    <button type="button" @click="cancelAction" class="text-xs text-muted-foreground hover:text-foreground">Cancel</button>
                </div>

                <!-- Preview -->
                <div class="p-3 rounded-xl flex items-center gap-3 border border-border bg-card">
                    <div 
                        class="flex h-10 w-10 items-center justify-center rounded-full shrink-0"
                        :style="{ backgroundColor: `${createForm.color}20`, color: createForm.color }"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(createForm.icon)"></svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-foreground">{{ createForm.name || 'Category Name' }}</div>
                        <div class="text-xs capitalize text-muted-foreground">{{ createForm.type }} Category</div>
                    </div>
                </div>

                <!-- Type Selector -->
                <div class="grid grid-cols-2 gap-2">
                    <button 
                        type="button" 
                        @click="createForm.type = 'expense'"
                        :class="[
                            'py-1.5 text-xs font-semibold rounded-lg border transition-all',
                            createForm.type === 'expense' ? 'bg-primary/10 border-primary text-primary' : 'border-border text-muted-foreground'
                        ]"
                    >
                        Expense
                    </button>
                    <button 
                        type="button" 
                        @click="createForm.type = 'income'"
                        :class="[
                            'py-1.5 text-xs font-semibold rounded-lg border transition-all',
                            createForm.type === 'income' ? 'bg-primary/10 border-primary text-primary' : 'border-border text-muted-foreground'
                        ]"
                    >
                        Income
                    </button>
                </div>

                <!-- Name & Emojis -->
                <div>
                    <InputLabel for="create-name" value="Name" />
                    <TextInput id="create-name" v-model="createForm.name" class="mt-1 block w-full text-sm" placeholder="e.g. 🍿 Entertainment" required />
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        <button
                            v-for="emoji in emojiSuggestions.slice(0, 10)"
                            :key="emoji"
                            type="button"
                            @click="addEmojiToCreate(emoji)"
                            class="text-xs px-1.5 py-0.5 rounded bg-muted hover:bg-muted/80 transition-colors"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                    <InputError class="mt-1" :message="createForm.errors.name" />
                </div>

                <!-- Color Selection -->
                <div>
                    <InputLabel value="Color Tag" />
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        <button
                            v-for="color in colors"
                            :key="color.value"
                            type="button"
                            @click="createForm.color = color.value"
                            class="h-6 w-6 rounded-full border-2 transition-transform"
                            :class="createForm.color === color.value ? 'scale-110 border-foreground shadow' : 'border-transparent hover:scale-105'"
                            :style="{ backgroundColor: color.value }"
                        />
                    </div>
                </div>

                <!-- Icon Selection -->
                <div>
                    <InputLabel value="Icon" />
                    <div class="flex flex-wrap gap-1.5 mt-1 max-h-24 overflow-y-auto">
                        <button
                            v-for="icon in icons"
                            :key="icon.value"
                            type="button"
                            @click="createForm.icon = icon.value"
                            class="p-1.5 rounded-lg border transition-all"
                            :class="createForm.icon === icon.value ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground hover:bg-muted'"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(icon.value)"></svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2">
                    <SecondaryButton @click="cancelAction" class="text-xs py-1.5 px-3">Cancel</SecondaryButton>
                    <PrimaryButton @click="submitCreate" :disabled="createForm.processing" class="text-xs py-1.5 px-4">
                        Save Category
                    </PrimaryButton>
                </div>
            </div>

            <!-- Edit Category Form Drawer -->
            <div v-else-if="editingCategory" class="mb-6 p-4 rounded-xl border border-border bg-muted/20 animate-fade-in space-y-4">
                <div class="flex items-center justify-between">
                    <h3 class="text-sm font-semibold text-foreground">Edit Category: {{ editingCategory.name }}</h3>
                    <button type="button" @click="cancelAction" class="text-xs text-muted-foreground hover:text-foreground">Cancel</button>
                </div>

                <!-- Preview -->
                <div class="p-3 rounded-xl flex items-center gap-3 border border-border bg-card">
                    <div 
                        class="flex h-10 w-10 items-center justify-center rounded-full shrink-0"
                        :style="{ backgroundColor: `${editForm.color}20`, color: editForm.color }"
                    >
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(editForm.icon)"></svg>
                    </div>
                    <div>
                        <div class="font-bold text-sm text-foreground">{{ editForm.name || 'Category Name' }}</div>
                        <div class="text-xs capitalize text-muted-foreground">{{ editForm.type }} Category</div>
                    </div>
                </div>

                <!-- Name & Emojis -->
                <div>
                    <InputLabel for="edit-name" value="Name" />
                    <TextInput id="edit-name" v-model="editForm.name" class="mt-1 block w-full text-sm" required />
                    <div class="mt-1.5 flex flex-wrap gap-1">
                        <button
                            v-for="emoji in emojiSuggestions.slice(0, 10)"
                            :key="emoji"
                            type="button"
                            @click="addEmojiToEdit(emoji)"
                            class="text-xs px-1.5 py-0.5 rounded bg-muted hover:bg-muted/80 transition-colors"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                    <InputError class="mt-1" :message="editForm.errors.name" />
                </div>

                <!-- Color Selection -->
                <div>
                    <InputLabel value="Color Tag" />
                    <div class="flex flex-wrap gap-1.5 mt-1">
                        <button
                            v-for="color in colors"
                            :key="color.value"
                            type="button"
                            @click="editForm.color = color.value"
                            class="h-6 w-6 rounded-full border-2 transition-transform"
                            :class="editForm.color === color.value ? 'scale-110 border-foreground shadow' : 'border-transparent hover:scale-105'"
                            :style="{ backgroundColor: color.value }"
                        />
                    </div>
                </div>

                <!-- Icon Selection -->
                <div>
                    <InputLabel value="Icon" />
                    <div class="flex flex-wrap gap-1.5 mt-1 max-h-24 overflow-y-auto">
                        <button
                            v-for="icon in icons"
                            :key="icon.value"
                            type="button"
                            @click="editForm.icon = icon.value"
                            class="p-1.5 rounded-lg border transition-all"
                            :class="editForm.icon === icon.value ? 'border-primary bg-primary/10 text-primary' : 'border-border text-muted-foreground hover:bg-muted'"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(icon.value)"></svg>
                        </button>
                    </div>
                </div>

                <div class="flex justify-between items-center pt-2">
                    <DangerButton @click="deleteCategory(editingCategory)" type="button" class="text-xs py-1.5 px-3">
                        Delete
                    </DangerButton>
                    <div class="flex gap-2">
                        <SecondaryButton @click="cancelAction" class="text-xs py-1.5 px-3">Cancel</SecondaryButton>
                        <PrimaryButton @click="submitEdit" :disabled="editForm.processing" class="text-xs py-1.5 px-4">
                            Update
                        </PrimaryButton>
                    </div>
                </div>
            </div>

            <!-- Categories List Filter Tabs -->
            <div v-if="!isCreating && !editingCategory" class="space-y-3">
                <div class="flex border-b border-border text-xs font-semibold">
                    <button 
                        @click="activeFilter = 'all'" 
                        :class="['pb-2 px-3 transition-colors border-b-2', activeFilter === 'all' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground']"
                    >
                        All ({{ categories.length }})
                    </button>
                    <button 
                        @click="activeFilter = 'expense'" 
                        :class="['pb-2 px-3 transition-colors border-b-2', activeFilter === 'expense' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground']"
                    >
                        Expense
                    </button>
                    <button 
                        @click="activeFilter = 'income'" 
                        :class="['pb-2 px-3 transition-colors border-b-2', activeFilter === 'income' ? 'border-primary text-primary' : 'border-transparent text-muted-foreground hover:text-foreground']"
                    >
                        Income
                    </button>
                </div>

                <!-- Categories Scroll Area -->
                <div class="max-h-[340px] overflow-y-auto space-y-2 pr-1">
                    <div
                        v-for="cat in filteredCategories"
                        :key="cat.id"
                        class="p-2.5 rounded-xl border border-border/60 bg-card hover:bg-muted/30 transition-all flex items-center justify-between gap-3 group"
                    >
                        <div class="flex items-center gap-3 min-w-0">
                            <div 
                                class="flex h-8 w-8 items-center justify-center rounded-lg shrink-0"
                                :style="{ backgroundColor: `${cat.color}20`, color: cat.color }"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(cat.icon)"></svg>
                            </div>
                            <div class="truncate">
                                <div class="text-sm font-medium text-foreground truncate">{{ cat.name }}</div>
                                <div class="text-[10px] text-muted-foreground capitalize">{{ cat.type }}</div>
                            </div>
                        </div>

                        <div class="flex items-center gap-1">
                            <button
                                @click="startEdit(cat)"
                                class="p-1.5 rounded-lg text-muted-foreground hover:text-foreground hover:bg-muted transition-colors"
                                title="Edit Category"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M12 20h9"/>
                                    <path d="M16.5 3.5a2.121 2.121 0 0 1 3 3L7 19l-4 1 1-4L16.5 3.5z"/>
                                </svg>
                            </button>
                            <button
                                @click="deleteCategory(cat)"
                                class="p-1.5 rounded-lg text-muted-foreground hover:text-destructive hover:bg-destructive/10 transition-colors"
                                title="Delete Category"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M3 6h18"/>
                                    <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/>
                                    <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="mt-6 flex justify-end pt-4 border-t border-border">
                <SecondaryButton @click="close">Close</SecondaryButton>
            </div>
        </div>
    </Modal>
</template>
