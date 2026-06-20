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
import AddCategoryModal from './AddCategoryModal.vue';
import { useCurrency } from '@/Composables/useCurrency';

const { formatMoney } = useCurrency();

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
    },
    recurringTransaction: {
        type: Object,
        default: null,
    }
});

const emit = defineEmits(['close']);

const showCreateCategoryModal = ref(false);

const form = useForm({
    title: '',
    amount: '',
    type: 'expense',
    frequency: 'monthly',
    next_run_date: new Date().toISOString().split('T')[0],
    category_id: '',
    card_id: '',
    recipient_name: '',
    notes: '',
    is_active: true,
});

const submit = () => {
    if (props.recurringTransaction) {
        form.put(route('transactions.recurring.update', props.recurringTransaction.id), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    } else {
        form.post(route('transactions.recurring.store'), {
            preserveScroll: true,
            onSuccess: () => {
                form.reset();
                emit('close');
            },
        });
    }
};

const close = () => {
    form.reset();
    form.clearErrors();
    emit('close');
};

watch(() => props.show, (newVal) => {
    if (newVal) {
        if (props.recurringTransaction) {
            form.title = props.recurringTransaction.title;
            form.amount = (props.recurringTransaction.amount / 100).toString();
            form.type = props.recurringTransaction.type;
            form.frequency = props.recurringTransaction.frequency;
            form.next_run_date = props.recurringTransaction.next_run_date;
            form.category_id = props.recurringTransaction.category ? props.recurringTransaction.category.id : '';
            form.card_id = props.recurringTransaction.card ? props.recurringTransaction.card.id : '';
            form.recipient_name = props.recurringTransaction.recipient_name || '';
            form.notes = props.recurringTransaction.notes || '';
            form.is_active = props.recurringTransaction.is_active;
        } else {
            form.title = '';
            form.amount = '';
            form.type = 'expense';
            form.frequency = 'monthly';
            form.next_run_date = new Date().toISOString().split('T')[0];
            form.category_id = '';
            form.card_id = '';
            form.recipient_name = '';
            form.notes = '';
            form.is_active = true;
        }
    }
});

const categoryOptions = computed(() => {
    const filtered = props.categories.filter(cat => cat.type === form.type);
    return [
        ...filtered.map(cat => ({ value: cat.id, label: cat.name })),
        { value: 'CREATE_NEW_CATEGORY', label: '➕ Add Category' }
    ];
});

const getColorEmoji = (hex) => {
    if (!hex) return '💳';
    const h = hex.toLowerCase();
    if (['#8b5cf6', '#6366f1'].includes(h)) return '🟣';
    if (['#3b82f6', '#38bdf8'].includes(h)) return '🔵';
    if (['#10b981', '#14b8a6'].includes(h)) return '🟢';
    if (['#f97316', '#fd822b'].includes(h)) return '🟠';
    if (['#f43f5e', '#ec4899', '#ff477e', '#e54b4f'].includes(h)) return '🔴';
    if (['#fdbe2a'].includes(h)) return '🟡';
    return '⚫';
};

const cardOptions = computed(() => {
    return [
        { value: '', label: '💵 Cash / Other' },
        ...props.cards.map(card => {
            const emoji = getColorEmoji(card.color);
            return {
                value: card.id,
                label: `${emoji} ${card.brand} (•••• ${card.last_four})`
            };
        })
    ];
});

const frequencyOptions = [
    { value: 'daily', label: '🔄 Daily' },
    { value: 'weekly', label: '📅 Weekly' },
    { value: 'bi-weekly', label: '📅 Bi-Weekly' },
    { value: 'monthly', label: '🗓 Monthly' },
    { value: 'yearly', label: '⏳ Yearly' }
];

watch(() => form.category_id, (newVal) => {
    if (newVal === 'CREATE_NEW_CATEGORY') {
        form.category_id = '';
        showCreateCategoryModal.value = true;
    }
});

watch(() => form.type, () => {
    form.category_id = '';
});

watch(() => props.categories, (newCats, oldCats) => {
    if (oldCats && newCats.length > oldCats.length) {
        const added = newCats.find(newCat => !oldCats.some(oldCat => oldCat.id === newCat.id));
        if (added && added.type === form.type) {
            form.category_id = added.id;
        }
    }
}, { deep: true });
</script>

<template>
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-foreground mb-6">
                {{ recurringTransaction ? 'Edit Recurring Template' : 'Create Recurring Template' }}
            </h2>

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
                        placeholder="e.g. Monthly Rent, Salary, Netflix"
                    />
                    <InputError class="mt-2" :message="form.errors.title" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="amount" value="Amount" />
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <span class="text-muted-foreground sm:text-sm">₱</span>
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
                        <InputLabel for="frequency" value="Frequency" />
                        <CustomSelect
                            id="frequency"
                            v-model="form.frequency"
                            :options="frequencyOptions"
                            class="mt-1"
                            trigger-class="h-11 rounded-xl"
                        />
                        <InputError class="mt-2" :message="form.errors.frequency" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="next_run_date" value="Next Run Date" />
                        <TextInput
                            id="next_run_date"
                            type="date"
                            class="mt-1 block w-full"
                            v-model="form.next_run_date"
                            required
                        />
                        <InputError class="mt-2" :message="form.errors.next_run_date" />
                    </div>

                    <div>
                        <InputLabel for="recipient_name" value="Recipient/Source" />
                        <TextInput
                            id="recipient_name"
                            type="text"
                            class="mt-1 block w-full"
                            v-model="form.recipient_name"
                            placeholder="e.g. Landlord, Acme Corp"
                        />
                        <InputError class="mt-2" :message="form.errors.recipient_name" />
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

                <!-- Active Toggle status -->
                <div class="flex items-center gap-3 py-2">
                    <input 
                        id="is_active" 
                        type="checkbox" 
                        v-model="form.is_active"
                        class="h-4 w-4 rounded border-gray-300 text-primary focus:ring-primary/30"
                    />
                    <label for="is_active" class="text-sm font-medium text-foreground select-none cursor-pointer">
                        Active Schedule (Auto-generate transactions on next run date)
                    </label>
                    <InputError class="mt-2" :message="form.errors.is_active" />
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
                        {{ form.processing ? 'Saving...' : (recurringTransaction ? 'Save Changes' : 'Create Template') }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>

    <AddCategoryModal
        :show="showCreateCategoryModal"
        :type="form.type"
        @close="showCreateCategoryModal = false"
    />
</template>
