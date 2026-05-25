<script setup>
import { ref, watch, computed } from 'vue';
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import CustomSelect from '@/Components/CustomSelect.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    categories: {
        type: Array,
        required: true,
    },
    cards: {
        type: Array,
        required: true,
    }
});

const emit = defineEmits(['close']);

const form = useForm({
    title: '',
    amount: '',
    type: 'expense',
    transaction_date: new Date().toISOString().split('T')[0],
    category_id: '',
    card_id: '',
    notes: '',
});

const submit = () => {
    form.post(route('transactions.store'), {
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

const categoryOptions = computed(() => {
    return props.categories.map(cat => ({ value: cat.id, label: cat.name }));
});

const cardOptions = computed(() => {
    return [
        { value: '', label: 'Cash / Other' },
        ...props.cards.map(card => ({ value: card.id, label: `${card.brand} •••• ${card.last_four}` }))
    ];
});
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-foreground mb-6">Add Transaction</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <!-- Type Selection -->
                <div class="grid grid-cols-2 gap-3 mb-6">
                    <button 
                        type="button" 
                        @click="form.type = 'expense'"
                        :class="[
                            'h-11 rounded-xl font-semibold transition-all duration-200 border-2',
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
                            'h-11 rounded-xl font-semibold transition-all duration-200 border-2',
                            form.type === 'income' 
                                ? 'bg-chart-1/10 border-chart-1 text-chart-1' 
                                : 'bg-input border-transparent text-muted-foreground hover:bg-input/80'
                        ]"
                    >
                        Income
                    </button>
                </div>

                <div>
                    <InputLabel for="title" value="Title" />
                    <TextInput
                        id="title"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.title"
                        required
                        placeholder="What was this for?"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="grid grid-cols-2 gap-4">
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
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.amount" />
                    </div>

                    <div>
                        <InputLabel for="transaction_date" value="Date" />
                        <TextInput
                            id="transaction_date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="form.transaction_date"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.transaction_date" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="category_id" value="Category" />
                        <CustomSelect
                            id="category_id"
                            v-model="form.category_id"
                            :options="categoryOptions"
                            placeholder="Select category"
                            class="mt-1"
                            trigger-class="h-11 rounded-xl"
                        />
                        <InputError class="mt-2" :message="form.errors.category_id" />
                    </div>

                    <div>
                        <InputLabel for="card_id" value="Payment Method" />
                        <CustomSelect
                            id="card_id"
                            v-model="form.card_id"
                            :options="cardOptions"
                            class="mt-1"
                            trigger-class="h-11 rounded-xl"
                        />
                        <InputError class="mt-2" :message="form.errors.card_id" />
                    </div>
                </div>

                <div>
                    <InputLabel for="notes" value="Notes (Optional)" />
                    <textarea
                        id="notes"
                        v-model="form.notes"
                        rows="2"
                        class="mt-1 w-full rounded-xl border-0 bg-input p-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all duration-200"
                        placeholder="Any additional details..."
                    ></textarea>
                    <InputError class="mt-2" :message="form.errors.notes" />
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-border">
                    <SecondaryButton @click="close" type="button">Cancel</SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Transaction' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
