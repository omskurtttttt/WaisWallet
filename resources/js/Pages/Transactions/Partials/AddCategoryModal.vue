<script setup>
import { computed, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { useIcons } from '@/Composables/useIcons';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    type: {
        type: String,
        default: 'expense', // 'expense' or 'income'
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    type: props.type,
    icon: 'tag',
    color: '#3b82f6', // Default blue
});

// Keep form type in sync with props
watch(() => props.type, (newType) => {
    form.type = newType;
});

const { getIconSvg } = useIcons();

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
    { name: 'Tag/Label', value: 'tag' },
    { name: 'Food/Drink', value: 'coffee' },
    { name: 'Shopping', value: 'shopping-cart' },
    { name: 'Transport', value: 'car' },
    { name: 'Home/Bills', value: 'home' },
    { name: 'Power/Utilities', value: 'zap' },
    { name: 'Movies/Fun', value: 'film' },
    { name: 'Health', value: 'heart-pulse' },
    { name: 'Education', value: 'graduation-cap' },
    { name: 'Bags/Store', value: 'shopping-bag' },
    { name: 'Travel', value: 'plane' },
    { name: 'Work/Briefcase', value: 'briefcase' },
    { name: 'Computer/Web', value: 'monitor' },
    { name: 'Gifts', value: 'gift' },
    { name: 'Growth/Invest', value: 'trending-up' },
];

const emojiSuggestions = [
    '🍔', '🛒', '🚗', '🏠', '🎮', '🏥', '📚', '🛍', '✈', '💸',
    '💼', '🧑‍💻', '📈', '🎁', '💰', '🪙', '🥤', '🍿', '🎨', '📱'
];

const addEmojiPrefix = (emoji) => {
    // Remove existing leading emoji if any
    let cleaned = form.name.trim();
    const emojiRegex = /^\p{Emoji}\s*/u;
    cleaned = cleaned.replace(emojiRegex, '');
    
    // Add new emoji prefix
    form.name = `${emoji} ${cleaned}`;
};

const submit = () => {
    form.post(route('categories.store'), {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            emit('close');
        },
    });
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="sm">
        <div class="p-6 relative z-50">
            <h2 class="text-xl font-bold text-foreground mb-6">Add New Category</h2>

            <form @submit.prevent="submit" class="space-y-4">
                
                <!-- Category Preview Card -->
                <div class="p-4 rounded-xl flex items-center gap-3 border border-border bg-muted/30">
                    <div 
                        class="flex h-12 w-12 items-center justify-center rounded-full shrink-0"
                        :style="{ backgroundColor: `${form.color}20`, color: form.color }"
                    >
                        <svg 
                            class="h-6 w-6" 
                            viewBox="0 0 24 24" 
                            fill="none" 
                            stroke="currentColor" 
                            stroke-width="2.5" 
                            stroke-linecap="round" 
                            stroke-linejoin="round"
                            v-html="getIconSvg(form.icon)"
                        ></svg>
                    </div>
                    <div>
                        <p class="text-xs text-muted-foreground uppercase font-bold tracking-wider">{{ form.type }} preview</p>
                        <p class="text-base font-semibold text-foreground truncate max-w-[200px]">
                            {{ form.name || 'Category Name' }}
                        </p>
                    </div>
                </div>

                <!-- Category Name -->
                <div>
                    <InputLabel for="category_name" value="Category Name" />
                    <TextInput
                        id="category_name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        placeholder="e.g. Subscriptions, Laundry"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <!-- Emoji Helper Pills -->
                <div>
                    <InputLabel value="Tap to prefix with Emoji" class="text-xs" />
                    <div class="mt-1.5 flex flex-wrap gap-1.5 max-h-24 overflow-y-auto p-1 bg-input/50 rounded-xl border border-border scrollbar-thin">
                        <button
                            v-for="emoji in emojiSuggestions"
                            :key="emoji"
                            type="button"
                            @click="addEmojiPrefix(emoji)"
                            class="h-8 w-8 text-lg flex items-center justify-center rounded-lg hover:bg-muted active:scale-95 transition-all focus:outline-none"
                        >
                            {{ emoji }}
                        </button>
                    </div>
                </div>

                <!-- Category Type -->
                <div>
                    <InputLabel value="Category Type" />
                    <div class="grid grid-cols-2 gap-3 mt-1">
                        <button 
                            type="button" 
                            @click="form.type = 'expense'"
                            :class="[
                                'h-10 rounded-xl font-semibold transition-all border-2 text-sm',
                                form.type === 'expense' 
                                    ? 'bg-destructive/10 border-destructive text-destructive' 
                                    : 'bg-input border-transparent text-muted-foreground hover:bg-input/80'
                            ]"
                        >
                            Expense
                        </button>
                        <button 
                            type="button" 
                            @click="form.type = 'income'"
                            :class="[
                                'h-10 rounded-xl font-semibold transition-all border-2 text-sm',
                                form.type === 'income' 
                                    ? 'bg-chart-1/10 border-chart-1 text-chart-1' 
                                    : 'bg-input border-transparent text-muted-foreground hover:bg-input/80'
                            ]"
                        >
                            Income
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.type" />
                </div>

                <!-- Theme Color -->
                <div>
                    <InputLabel value="Theme Color" />
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="color in colors"
                            :key="color.value"
                            type="button"
                            @click="form.color = color.value"
                            class="h-7 w-7 rounded-full border-2 transition-all focus:outline-none"
                            :class="form.color === color.value ? 'border-foreground scale-110' : 'border-transparent hover:scale-110'"
                            :style="{ backgroundColor: color.value, outlineColor: color.value }"
                            :title="color.name"
                        ></button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.color" />
                </div>

                <!-- Icon Picker -->
                <div>
                    <InputLabel value="Select Icon" />
                    <div class="mt-2 grid grid-cols-5 gap-2 p-2 bg-input/50 rounded-xl border border-border">
                        <button
                            v-for="icon in icons"
                            :key="icon.value"
                            type="button"
                            @click="form.icon = icon.value"
                            class="flex h-10 w-10 items-center justify-center rounded-lg border-2 transition-all hover:bg-muted focus:outline-none"
                            :class="form.icon === icon.value ? 'border-primary bg-primary/10 text-primary' : 'border-transparent text-muted-foreground'"
                            :title="icon.name"
                        >
                            <svg 
                                class="h-5 w-5" 
                                viewBox="0 0 24 24" 
                                fill="none" 
                                stroke="currentColor" 
                                stroke-width="2" 
                                stroke-linecap="round" 
                                stroke-linejoin="round"
                                v-html="getIconSvg(icon.value)"
                            ></svg>
                        </button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.icon" />
                </div>

                <!-- Action Buttons -->
                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-border">
                    <SecondaryButton @click="close" type="button">Cancel</SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Add Category' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
