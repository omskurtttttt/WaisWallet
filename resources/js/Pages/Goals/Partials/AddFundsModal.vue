<script setup>
import { ref, watch } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    goal: {
        type: Object,
        default: null,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    amount: '',
    operation: 'add', // add or withdraw
});

// Watch for goal changes to reset the form state
watch(() => props.show, (newVal) => {
    if (newVal) {
        form.reset();
        form.clearErrors();
    }
});

const submit = () => {
    if (!props.goal) return;
    
    form.post(route('goals.funds', props.goal.id), {
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
        <div class="p-6" v-if="goal">
            <h2 class="text-xl font-bold text-foreground mb-1">Adjust Savings</h2>
            <p class="text-sm text-muted-foreground mb-6">Updating funds for: <span class="font-semibold text-foreground">{{ goal.name }}</span></p>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Action Type Toggle -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button 
                        type="button" 
                        @click="form.operation = 'add'"
                        :class="[
                            'h-11 rounded-xl font-semibold transition-all duration-200 border-2',
                            form.operation === 'add' 
                                ? 'bg-primary/10 border-primary text-primary' 
                                : 'bg-input border-transparent text-muted-foreground hover:bg-input/80'
                        ]"
                    >
                        Deposit
                    </button>
                    <button 
                        type="button" 
                        @click="form.operation = 'withdraw'"
                        :class="[
                            'h-11 rounded-xl font-semibold transition-all duration-200 border-2',
                            form.operation === 'withdraw' 
                                ? 'bg-destructive/10 border-destructive text-destructive' 
                                : 'bg-input border-transparent text-muted-foreground hover:bg-input/80'
                        ]"
                    >
                        Withdraw
                    </button>
                </div>

                <div>
                    <InputLabel for="amount" value="Amount" />
                    <div class="relative mt-1">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                            <span class="text-muted-foreground sm:text-sm">$</span>
                        </div>
                        <input
                            id="amount"
                            type="number"
                            step="0.01"
                            min="0.01"
                            class="h-11 w-full rounded-xl border-0 bg-input pl-8 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all duration-200"
                            v-model="form.amount"
                            required
                            placeholder="0.00"
                            autofocus
                        />
                    </div>
                    <InputError class="mt-2" :message="form.errors.amount" />
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-border">
                    <SecondaryButton @click="close" type="button">Cancel</SecondaryButton>
                    <PrimaryButton
                        :class="[
                            'transition-all duration-200',
                            form.processing ? 'opacity-50 cursor-not-allowed' : '',
                            form.operation === 'withdraw' ? 'bg-destructive hover:bg-destructive/90 focus:ring-destructive' : ''
                        ]"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Processing...' : (form.operation === 'add' ? 'Confirm Deposit' : 'Confirm Withdrawal') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
