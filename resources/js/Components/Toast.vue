<script setup>
import { ref, watch } from 'vue';
import { usePage } from '@inertiajs/vue3';

const page = usePage();
const show = ref(false);
const message = ref('');
const type = ref('success'); // success or error
let timeout = null;

const showToast = (msg, toastType = 'success') => {
    message.value = msg;
    type.value = toastType;
    show.value = true;

    if (timeout) clearTimeout(timeout);
    timeout = setTimeout(() => {
        show.value = false;
    }, 4000);
};

// Watch for flash messages from Inertia
watch(
    () => page.props.flash,
    (flash) => {
        if (flash?.success) {
            showToast(flash.success, 'success');
        } else if (flash?.error) {
            showToast(flash.error, 'error');
        }
    },
    { deep: true, immediate: true }
);
</script>

<template>
    <transition
        enter-active-class="transform ease-out duration-300 transition"
        enter-from-class="translate-y-2 opacity-0 sm:translate-y-0 sm:translate-x-2"
        enter-to-class="translate-y-0 opacity-100 sm:translate-x-0"
        leave-active-class="transition ease-in duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="show"
            class="fixed bottom-5 right-5 z-[100] flex w-full max-w-sm overflow-hidden rounded-[16px] bg-card border border-border p-4 card-shadow transition-theme"
        >
            <div class="flex items-start w-full gap-3">
                <!-- Icon -->
                <div class="flex shrink-0">
                    <div
                        v-if="type === 'success'"
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-chart-1/10 text-chart-1"
                    >
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <polyline points="20 6 9 17 4 12" />
                        </svg>
                    </div>
                    <div
                        v-else
                        class="flex h-8 w-8 items-center justify-center rounded-xl bg-destructive/10 text-destructive"
                    >
                        <svg class="h-4.5 w-4.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <line x1="18" y1="6" x2="6" y2="18" />
                            <line x1="6" y1="6" x2="18" y2="18" />
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <div class="flex-1 pt-0.5">
                    <p class="text-sm font-bold text-foreground">
                        {{ type === 'success' ? 'Success' : 'Error' }}
                    </p>
                    <p class="mt-0.5 text-xs text-muted-foreground font-medium leading-relaxed">
                        {{ message }}
                    </p>
                </div>

                <!-- Close Button -->
                <button
                    @click="show = false"
                    class="flex h-6 w-6 shrink-0 items-center justify-center rounded-lg text-muted-foreground hover:bg-muted hover:text-foreground transition-all duration-200"
                >
                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <line x1="18" y1="6" x2="6" y2="18" />
                    </svg>
                </button>
            </div>
        </div>
    </transition>
</template>
