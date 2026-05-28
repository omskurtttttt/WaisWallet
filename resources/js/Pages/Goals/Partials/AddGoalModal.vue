<script setup>
import { useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    show: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    target_amount: '',
    current_amount: '',
    target_date: '',
});

const submit = () => {
    form.post(route('goals.store'), {
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
    <Modal :show="show" @close="close" maxWidth="md">
        <div class="p-6">
            <h2 class="text-xl font-bold text-foreground mb-6">Create Savings Goal</h2>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <InputLabel for="name" value="Goal Name" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        placeholder="e.g. New Laptop, Emergency Fund, Summer Trip"
                        autofocus
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="target_amount" value="Target Amount" />
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <span class="text-muted-foreground sm:text-sm">₱</span>
                            </div>
                            <input
                                id="target_amount"
                                type="number"
                                step="0.01"
                                min="0.01"
                                class="h-11 w-full rounded-xl border-0 bg-input pl-8 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all duration-200"
                                v-model="form.target_amount"
                                required
                                placeholder="0.00"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.target_amount" />
                    </div>

                    <div>
                        <InputLabel for="current_amount" value="Current Savings (Optional)" />
                        <div class="relative mt-1">
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-4">
                                <span class="text-muted-foreground sm:text-sm">₱</span>
                            </div>
                            <input
                                id="current_amount"
                                type="number"
                                step="0.01"
                                min="0"
                                class="h-11 w-full rounded-xl border-0 bg-input pl-8 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all duration-200"
                                v-model="form.current_amount"
                                placeholder="0.00"
                            />
                        </div>
                        <InputError class="mt-2" :message="form.errors.current_amount" />
                    </div>
                </div>

                <div>
                    <InputLabel for="target_date" value="Target Date" />
                    <TextInput
                        id="target_date"
                        type="date"
                        class="mt-1 block w-full"
                        v-model="form.target_date"
                        required
                        :min="new Date().toISOString().split('T')[0]"
                    />
                    <InputError class="mt-2" :message="form.errors.target_date" />
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-border">
                    <SecondaryButton @click="close" type="button">Cancel</SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Creating...' : 'Create Goal' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
