<script setup>
import { computed, watch } from 'vue';
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
});

const emit = defineEmits(['close']);

const form = useForm({
    name: '',
    brand: 'Visa',
    last_four: '',
    expiration_month: '',
    expiration_year: '',
    color: '#8b5cf6', // Default purple/violet for the accounts
    is_active: true,
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

const brands = ['Visa', 'Mastercard', 'Amex', 'Discover', 'GCash', 'Maya', 'GrabPay', 'Coins.ph', 'BPI', 'BDO', 'Metrobank', 'UnionBank', 'Cash', 'Other'];

const isCash = computed(() => form.brand === 'Cash');
const isEWallet = computed(() => ['gcash', 'maya', 'grabpay', 'coins.ph'].includes(form.brand?.toLowerCase()));
const showExpiry = computed(() => !isCash.value && !isEWallet.value);
const showLastFour = computed(() => !isCash.value);

watch(() => form.brand, (newBrand) => {
    if (newBrand === 'Cash') {
        form.last_four = '0000';
        form.expiration_month = 12;
        form.expiration_year = 2099;
    } else if (['gcash', 'maya', 'grabpay', 'coins.ph'].includes(newBrand.toLowerCase())) {
        form.expiration_month = 12;
        form.expiration_year = 2099;
        if (form.last_four === '0000') {
            form.last_four = '';
        }
    }
});

const lastFourLabel = computed(() => {
    if (isEWallet.value) {
        return 'Last 4 Digits of Mobile No.';
    }
    if (['bpi', 'bdo', 'metrobank', 'unionbank'].includes(form.brand?.toLowerCase())) {
        return 'Last 4 Digits of Account No.';
    }
    return 'Last 4 Digits';
});

const submit = () => {
    form.post(route('wallet.cards.store'), {
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
            <h2 class="text-xl font-bold text-foreground mb-6">Add New Account</h2>

            <form @submit.prevent="submit" class="space-y-4">
                
                <!-- Card Preview -->
                <div class="mb-6 relative h-40 w-full rounded-2xl overflow-hidden shadow-lg transition-colors duration-300"
                     :style="{ background: `linear-gradient(135deg, ${form.color} 0%, ${form.color}dd 100%)` }">
                    <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -translate-y-1/2 translate-x-1/2"></div>
                    <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-white/5 translate-y-1/2 -translate-x-1/2"></div>
                    
                    <div class="relative z-10 flex flex-col justify-between h-full p-5 text-white">
                        <div class="flex justify-between items-start">
                            <!-- Icon selection based on type -->
                            <svg v-if="isCash" class="h-8 w-8 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="6" width="20" height="12" rx="2" />
                                <circle cx="12" cy="12" r="2" />
                                <path d="M6 12h.01M18 12h.01" />
                            </svg>
                            <svg v-else-if="isEWallet" class="h-8 w-8 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                            </svg>
                            <svg v-else class="h-8 w-8 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <line x1="2" y1="10" x2="22" y2="10" />
                                <line x1="7" y1="5" x2="7" y2="10" />
                                <line x1="17" y1="5" x2="17" y2="10" />
                            </svg>
                            <span class="text-sm font-bold italic opacity-90">{{ form.brand }}</span>
                        </div>
                        
                        <div>
                            <p class="text-lg font-financial tracking-widest mb-1 shadow-sm uppercase">
                                <span v-if="isCash">CASH ACCOUNT</span>
                                <span v-else>•••• •••• •••• {{ form.last_four || 'XXXX' }}</span>
                            </p>
                            <div class="flex justify-between items-end">
                                <div>
                                    <p class="text-[10px] uppercase opacity-70 tracking-wider">Account Name</p>
                                    <p class="text-sm font-medium">{{ form.name || (isCash ? 'My Cash' : 'My Account') }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-[10px] uppercase opacity-70 tracking-wider">{{ showExpiry ? 'Expires' : 'Status' }}</p>
                                    <p class="text-sm font-financial uppercase">
                                        <span v-if="showExpiry">
                                            {{ form.expiration_month ? form.expiration_month.toString().padStart(2, '0') : 'MM' }}/{{ form.expiration_year ? form.expiration_year.toString().slice(-2) : 'YY' }}
                                        </span>
                                        <span v-else class="font-bold tracking-wider text-[11px] bg-white/20 px-2 py-0.5 rounded">
                                            Active
                                        </span>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <InputLabel for="name" :value="isCash ? 'Account Name' : 'Account/Card Nickname'" />
                    <TextInput
                        id="name"
                        type="text"
                        class="mt-1 block w-full"
                        v-model="form.name"
                        required
                        :placeholder="isCash ? 'e.g. My Pocket Cash, Safe Box' : 'e.g. Personal Checkings, Travel Credit'"
                    />
                    <InputError class="mt-2" :message="form.errors.name" />
                </div>

                <div :class="showLastFour ? 'grid grid-cols-2 gap-4' : 'block'">
                    <div>
                        <InputLabel for="brand" value="Account Type / Provider" />
                        <CustomSelect
                            id="brand"
                            v-model="form.brand"
                            :options="brands"
                            class="mt-1"
                            trigger-class="h-11 rounded-xl"
                        />
                        <InputError class="mt-2" :message="form.errors.brand" />
                    </div>

                    <div v-if="showLastFour">
                        <InputLabel for="last_four" :value="lastFourLabel" />
                        <TextInput
                            id="last_four"
                            type="text"
                            inputmode="numeric"
                            maxlength="4"
                            class="mt-1 block w-full"
                            v-model="form.last_four"
                            required
                            placeholder="1234"
                            pattern="\d{4}"
                        />
                        <InputError class="mt-2" :message="form.errors.last_four" />
                    </div>
                </div>

                <div v-if="showExpiry" class="grid grid-cols-2 gap-4">
                    <div>
                        <InputLabel for="expiration_month" value="Exp Month (MM)" />
                        <TextInput
                            id="expiration_month"
                            type="number"
                            min="1"
                            max="12"
                            class="mt-1 block w-full"
                            v-model="form.expiration_month"
                            required
                            placeholder="01"
                        />
                        <InputError class="mt-2" :message="form.errors.expiration_month" />
                    </div>

                    <div>
                        <InputLabel for="expiration_year" value="Exp Year (YYYY)" />
                        <TextInput
                            id="expiration_year"
                            type="number"
                            :min="new Date().getFullYear()"
                            class="mt-1 block w-full"
                            v-model="form.expiration_year"
                            required
                            placeholder="2028"
                        />
                        <InputError class="mt-2" :message="form.errors.expiration_year" />
                    </div>
                </div>

                <div>
                    <InputLabel value="Theme Color" />
                    <div class="mt-2 flex flex-wrap gap-2">
                        <button
                            v-for="color in colors"
                            :key="color.value"
                            type="button"
                            @click="form.color = color.value"
                            class="h-8 w-8 rounded-full border-2 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-offset-background"
                            :class="form.color === color.value ? 'border-foreground scale-110' : 'border-transparent hover:scale-110'"
                            :style="{ backgroundColor: color.value, outlineColor: color.value }"
                            :title="color.name"
                        ></button>
                    </div>
                    <InputError class="mt-2" :message="form.errors.color" />
                </div>

                <div class="mt-6 flex justify-end gap-3 pt-4 border-t border-border">
                    <SecondaryButton @click="close" type="button">Cancel</SecondaryButton>
                    <PrimaryButton
                        :class="{ 'opacity-50 cursor-not-allowed': form.processing }"
                        :disabled="form.processing"
                    >
                        {{ form.processing ? 'Saving...' : 'Add Account' }}
                    </PrimaryButton>
                </div>
            </form>
        </div>
    </Modal>
</template>
