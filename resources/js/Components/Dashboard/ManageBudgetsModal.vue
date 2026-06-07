<script setup>
import { useForm } from '@inertiajs/vue3';
import { watch } from 'vue';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
    expenseCategories: {
        type: Array,
        required: true,
    },
    existingBudgets: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    month: '',
    budgets: [],
});

watch(
    () => props.show,
    (newVal) => {
        if (newVal) {
            // Set the current month in YYYY-MM format
            const today = new Date();
            const year = today.getFullYear();
            const month = String(today.getMonth() + 1).padStart(2, '0');
            form.month = `${year}-${month}`;

            // Initialize budgets array
            form.budgets = props.expenseCategories.map((category) => {
                const existingBudget = props.existingBudgets.find(
                    (b) => b.category_id === category.id
                );
                return {
                    category_id: category.id,
                    amount: existingBudget ? (existingBudget.allocated_amount / 100).toString() : '',
                    name: category.name,
                    color: category.color,
                };
            });
        }
    },
    { immediate: true }
);

const submit = () => {
    form.transform((data) => ({
        ...data,
        budgets: data.budgets.map((b) => ({
            category_id: b.category_id,
            amount: b.amount === '' ? null : Number(b.amount),
        })),
    })).post(route('budgets.store'), {
        preserveScroll: true,
        onSuccess: () => {
            close();
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
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-foreground">Manage Budgets</h2>
            <p class="mt-1 text-sm text-muted-foreground mb-6">
                Set monthly spending targets for your expense categories. Leave blank or set to 0 to remove.
            </p>

            <form @submit.prevent="submit" class="space-y-4">
                <div class="max-h-[350px] overflow-y-auto pr-1 space-y-3">
                    <div
                        v-for="(budget, index) in form.budgets"
                        :key="budget.category_id"
                        class="flex flex-col py-1"
                    >
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <span
                                    class="h-3 w-3 rounded-full shrink-0"
                                    :style="{ backgroundColor: budget.color }"
                                ></span>
                                <span class="text-sm font-medium text-foreground">{{ budget.name }}</span>
                            </div>
                            <div class="relative w-36">
                                <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                    <span class="text-muted-foreground text-xs font-semibold">₱</span>
                                </div>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0"
                                    class="h-9 w-full rounded-xl border-0 bg-input pl-7 pr-3 text-xs text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all duration-200"
                                    v-model="budget.amount"
                                    placeholder="No budget"
                                />
                            </div>
                        </div>
                        <div
                            v-if="form.errors[`budgets.${index}.amount`]"
                            class="text-[11px] text-destructive mt-1 text-right"
                        >
                            {{ form.errors[`budgets.${index}.amount`] }}
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-border">
                    <SecondaryButton @click="close" type="button">Cancel</SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Save Budgets' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
