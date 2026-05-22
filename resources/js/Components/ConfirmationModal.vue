<script setup>
import Modal from '@/Components/Modal.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    title: {
        type: String,
        required: true,
    },
    message: {
        type: String,
        required: true,
    },
    confirmButtonText: {
        type: String,
        default: 'Confirm',
    },
    cancelButtonText: {
        type: String,
        default: 'Cancel',
    },
    type: {
        type: String,
        default: 'danger', // danger, info, warning
    },
});

const emit = defineEmits(['confirm', 'cancel', 'close']);

const confirm = () => {
    emit('confirm');
};

const cancel = () => {
    emit('cancel');
    emit('close');
};
</script>

<template>
    <Modal :show="show" max-width="md" @close="cancel">
        <div class="p-6">
            <div class="flex items-start gap-4">
                <!-- Icon based on type -->
                <div 
                    :class="[
                        'flex h-12 w-12 shrink-0 items-center justify-center rounded-xl',
                        type === 'danger' ? 'bg-destructive/10 text-destructive' : '',
                        type === 'warning' ? 'bg-yellow-500/10 text-yellow-600' : '',
                        type === 'info' ? 'bg-primary/10 text-primary' : '',
                    ]"
                >
                    <!-- Danger / Warning Icon -->
                    <svg v-if="type === 'danger' || type === 'warning'" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="m21.73 18-8-14a2 2 0 0 0-3.48 0l-8 14A2 2 0 0 0 4 21h16a2 2 0 0 0 1.73-3Z"/>
                        <line x1="12" y1="9" x2="12" y2="13"/>
                        <line x1="12" y1="17" x2="12.01" y2="17"/>
                    </svg>
                    <!-- Info Icon -->
                    <svg v-else class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"/>
                        <line x1="12" y1="16" x2="12" y2="12"/>
                        <line x1="12" y1="8" x2="12.01" y2="8"/>
                    </svg>
                </div>

                <!-- Text Content -->
                <div class="flex-1 space-y-1">
                    <h3 class="text-lg font-bold text-foreground">{{ title }}</h3>
                    <p class="text-sm text-muted-foreground leading-relaxed">{{ message }}</p>
                </div>
            </div>

            <!-- Footer Buttons -->
            <div class="mt-6 flex justify-end gap-3">
                <SecondaryButton @click="cancel">
                    {{ cancelButtonText }}
                </SecondaryButton>
                
                <DangerButton 
                    v-if="type === 'danger'"
                    @click="confirm"
                    class="bg-destructive hover:bg-destructive/90 text-destructive-foreground focus:ring-destructive"
                >
                    {{ confirmButtonText }}
                </DangerButton>

                <button 
                    v-else
                    @click="confirm"
                    :class="[
                        'inline-flex items-center rounded-md border border-transparent px-4 py-2 text-xs font-semibold uppercase tracking-widest text-white transition duration-150 ease-in-out focus:outline-none focus:ring-2 focus:ring-offset-2',
                        type === 'warning' ? 'bg-yellow-600 hover:bg-yellow-500 focus:ring-yellow-500' : 'bg-primary hover:bg-primary/90 focus:ring-primary'
                    ]"
                >
                    {{ confirmButtonText }}
                </button>
            </div>
        </div>
    </Modal>
</template>
