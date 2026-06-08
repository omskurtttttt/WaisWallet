<script setup>
import { computed, ref, onMounted, onUnmounted } from 'vue';

const props = defineProps({
    modelValue: [String, Number, Boolean, Object],
    options: {
        type: Array,
        default: () => [],
    },
    placeholder: {
        type: String,
        default: 'Select option',
    },
    disabled: {
        type: Boolean,
        default: false,
    },
    triggerClass: {
        type: String,
        default: '',
    },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const containerRef = ref(null);

// Normalize options to [{ value, label, disabled }]
const normalizedOptions = computed(() => {
    return props.options.map((opt) => {
        if (typeof opt === 'object' && opt !== null) {
            return {
                value: opt.value !== undefined ? opt.value : opt.label,
                label: opt.label !== undefined ? opt.label : opt.value,
                disabled: !!opt.disabled,
            };
        }
        return {
            value: opt,
            label: opt,
            disabled: false,
        };
    });
});

// Find the currently selected option
const selectedOption = computed(() => {
    return normalizedOptions.value.find((opt) => opt.value === props.modelValue);
});

// Display label of the selected option or placeholder
const displayLabel = computed(() => {
    return selectedOption.value ? selectedOption.value.label : props.placeholder;
});

const toggleDropdown = () => {
    if (!props.disabled) {
        isOpen.value = !isOpen.value;
    }
};

const selectOption = (option) => {
    if (option.disabled) return;
    emit('update:modelValue', option.value);
    emit('change', option.value);
    isOpen.value = false;
};

const isSelected = (option) => {
    return props.modelValue === option.value;
};

// Handle clicks outside the dropdown container to close it
const handleClickOutside = (event) => {
    if (containerRef.value && !containerRef.value.contains(event.target)) {
        isOpen.value = false;
    }
};

// Keyboard listener for Escape to close
const handleKeyDown = (event) => {
    if (event.key === 'Escape' && isOpen.value) {
        isOpen.value = false;
    }
};

onMounted(() => {
    document.addEventListener('click', handleClickOutside);
    document.addEventListener('keydown', handleKeyDown);
});

onUnmounted(() => {
    document.removeEventListener('click', handleClickOutside);
    document.removeEventListener('keydown', handleKeyDown);
});
</script>

<template>
    <div ref="containerRef" class="relative inline-block w-full" :class="{ 'z-50': isOpen }">
        <!-- Trigger Button -->
        <button
            type="button"
            @click="toggleDropdown"
            class="flex w-full items-center justify-between border border-border bg-input px-3.5 text-left text-sm text-foreground transition-all duration-200 cursor-pointer focus:outline-none focus:ring-2 focus:ring-primary/30"
            :class="[
                disabled ? 'opacity-50 cursor-not-allowed' : 'hover:bg-input/80',
                isOpen ? 'ring-2 ring-primary/30' : '',
                triggerClass || 'h-10 rounded-xl'
            ]"
            aria-haspopup="listbox"
            :aria-expanded="isOpen"
        >
            <span class="truncate text-foreground select-none">{{ displayLabel }}</span>
            <svg
                class="ml-2 h-4 w-4 shrink-0 text-muted-foreground transition-transform duration-200"
                :class="{ 'rotate-180': isOpen }"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2"
                stroke-linecap="round"
                stroke-linejoin="round"
            >
                <polyline points="6 9 12 15 18 9"></polyline>
            </svg>
        </button>

        <!-- Dropdown Menu -->
        <Transition
            enter-active-class="transition ease-out duration-100"
            enter-from-class="opacity-0 scale-95"
            enter-to-class="opacity-100 scale-100"
            leave-active-class="transition ease-in duration-75"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 scale-95"
        >
            <div
                v-show="isOpen"
                class="absolute left-0 z-50 mt-1.5 max-h-60 min-w-full w-max max-w-[280px] overflow-y-auto rounded-xl border border-border bg-popover py-1 shadow-lg focus:outline-none scrollbar-thin"
                style="transform-origin: top;"
                role="listbox"
            >
                <div v-if="normalizedOptions.length === 0" class="px-3.5 py-2 text-sm text-muted-foreground italic">
                    No options available
                </div>
                <button
                    v-for="option in normalizedOptions"
                    :key="option.value"
                    type="button"
                    @click="selectOption(option)"
                    class="relative flex w-full cursor-pointer select-none items-center py-2.5 pl-9 pr-4 text-left text-sm text-foreground transition-colors hover:bg-muted focus:outline-none"
                    :class="[
                        option.disabled ? 'opacity-40 cursor-not-allowed' : '',
                        isSelected(option) ? 'font-medium text-foreground' : ''
                    ]"
                    :disabled="option.disabled"
                    role="option"
                    :aria-selected="isSelected(option)"
                >
                    <!-- Checkmark Column -->
                    <span
                        v-if="isSelected(option)"
                        class="absolute left-3 flex items-center justify-center text-primary"
                    >
                        <svg
                            class="h-4 w-4"
                            viewBox="0 0 24 24"
                            fill="none"
                            stroke="currentColor"
                            stroke-width="2.5"
                            stroke-linecap="round"
                            stroke-linejoin="round"
                        >
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                    </span>

                    <span class="truncate">{{ option.label }}</span>
                </button>
            </div>
        </Transition>
    </div>
</template>
