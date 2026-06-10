<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddCardModal from './Partials/AddCardModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useCardUtils } from '@/Composables/useCardUtils';

const props = defineProps({
    cards: Array,
});

const { formatMoney } = useCurrency();
const { getAccountCategory } = useCardUtils();
const showAddModal = ref(false);

const getBrandIcon = (brand) => {
    switch (brand?.toLowerCase()) {
        case 'visa': return 'VISA';
        case 'mastercard': return 'MC';
        case 'amex': return 'AMEX';
        case 'discover': return 'DISC';
        case 'gcash': return 'GCash';
        case 'maya': return 'Maya';
        case 'grabpay': return 'GrabPay';
        case 'coins.ph': return 'Coins.ph';
        default: return brand || 'CARD';
    }
};

const showDeleteConfirm = ref(false);
const cardToDelete = ref(null);

const confirmDeleteCard = (id) => {
    cardToDelete.value = id;
    showDeleteConfirm.value = true;
};

const deleteCard = () => {
    if (cardToDelete.value) {
        router.delete(route('wallet.cards.destroy', cardToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteConfirm.value = false;
                cardToDelete.value = null;
            },
            onError: (errors) => {
                console.error(errors);
                alert('Failed to remove account. Please try again.');
            }
        });
    }
};
</script>

<template>
    <Head title="Wallet" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Accounts & Wallets</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage your credit cards, debit cards, cash, and e-wallets.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-md shadow-primary/25"
                >
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Add Account</span>
                </button>
            </div>

            <!-- Cards Grid -->
            <div v-if="cards && cards.length > 0" class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.1s">
                <div v-for="card in cards" :key="card.id" class="group relative">
                    <!-- Card Display -->
                    <div class="relative h-56 w-full rounded-2xl overflow-hidden transition-all duration-300 shadow-lg hover:shadow-xl hover:-translate-y-1"
                         :style="{ background: `linear-gradient(135deg, ${card.color} 0%, ${card.color}dd 100%)` }">
                        <!-- Abstract Design Elements -->
                        <div class="absolute top-0 right-0 w-48 h-48 rounded-full bg-white/10 -translate-y-1/2 translate-x-1/2"></div>
                        <div class="absolute bottom-0 left-0 w-40 h-40 rounded-full bg-white/5 translate-y-1/2 -translate-x-1/2"></div>
                        
                        <!-- Card Content -->
                        <div class="relative z-10 flex flex-col justify-between h-full p-6 text-white">
                            <div class="flex justify-between items-start">
                                <!-- Dynamic Icon depending on category -->
                                <svg v-if="getAccountCategory(card.brand).icon === 'cash'" class="h-10 w-10 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="2" y="6" width="20" height="12" rx="2" />
                                    <circle cx="12" cy="12" r="2" />
                                    <path d="M6 12h.01M18 12h.01" />
                                </svg>
                                <svg v-else-if="getAccountCategory(card.brand).icon === 'wallet'" class="h-10 w-10 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <svg v-else-if="getAccountCategory(card.brand).icon === 'bank'" class="h-10 w-10 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <path d="M3 22V11M21 22V11M2 11L12 2l10 9M10 22v-5h4v5M4 11h16M12 5v2" />
                                </svg>
                                <svg v-else class="h-10 w-10 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <line x1="2" y1="10" x2="22" y2="10" />
                                    <line x1="7" y1="5" x2="7" y2="10" />
                                    <line x1="17" y1="5" x2="17" y2="10" />
                                </svg>
                                <span class="text-xl font-bold italic opacity-90 tracking-wider">{{ getBrandIcon(card.brand) }}</span>
                            </div>
                            
                            <div>
                                <p class="text-2xl font-bold font-financial tracking-tight mb-2 shadow-sm">
                                    {{ formatMoney(card.balance) }}
                                </p>
                                <div class="flex justify-between items-end">
                                    <div>
                                        <p class="text-[10px] uppercase opacity-70 tracking-wider mb-1">
                                            <span v-if="card.brand === 'Cash'">CASH ACCOUNT</span>
                                            <span v-else-if="['gcash', 'maya', 'grabpay', 'coins.ph'].includes(card.brand.toLowerCase())">E-WALLET ({{ card.last_four }})</span>
                                            <span v-else-if="['bpi', 'bdo', 'metrobank', 'unionbank'].includes(card.brand.toLowerCase())">BANK ACCT ({{ card.last_four }})</span>
                                            <span v-else>CARD ({{ card.last_four }})</span>
                                        </p>
                                        <p class="text-base font-medium truncate max-w-[150px]">{{ card.name }}</p>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-[10px] uppercase opacity-70 tracking-wider mb-1">
                                            {{ (card.brand === 'Cash' || ['gcash', 'maya', 'grabpay', 'coins.ph'].includes(card.brand.toLowerCase())) ? 'Status' : 'Expires' }}
                                        </p>
                                        <p class="text-base font-financial uppercase">
                                            <span v-if="card.brand === 'Cash' || ['gcash', 'maya', 'grabpay', 'coins.ph'].includes(card.brand.toLowerCase())" class="font-bold tracking-wider text-[11px] bg-white/20 px-2 py-0.5 rounded">
                                                Active
                                            </span>
                                            <span v-else>
                                                {{ card.expiration_month }}/{{ card.expiration_year.toString().slice(-2) }}
                                            </span>
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Actions Menu (Visible on hover) -->
                    <div class="absolute top-4 right-4 opacity-0 group-hover:opacity-100 transition-opacity duration-200 z-20">
                        <button @click="confirmDeleteCard(card.id)" class="flex h-8 w-8 items-center justify-center rounded-full bg-black/20 text-white backdrop-blur-md hover:bg-destructive hover:text-destructive-foreground transition-colors" title="Remove Account">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
                
                <!-- Add Card Button (Empty State card) -->
                <button @click="showAddModal = true" class="h-56 rounded-2xl border-2 border-dashed border-border bg-transparent flex flex-col items-center justify-center text-muted-foreground hover:bg-muted/30 hover:text-foreground transition-colors group">
                    <div class="h-12 w-12 rounded-full bg-muted flex items-center justify-center mb-3 group-hover:bg-primary/10 group-hover:text-primary transition-colors">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M5 12h14"/><path d="M12 5v14"/>
                        </svg>
                    </div>
                    <span class="font-medium">Add another account</span>
                </button>
            </div>
            
            <div v-else class="glass-card flex flex-col items-center justify-center py-20 px-4 text-center animate-slide-up">
                <div class="h-20 w-20 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-6">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="20" height="14" x="2" y="5" rx="2" />
                        <line x1="2" x2="22" y1="10" y2="10" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-foreground mb-2">No Accounts Added Yet</h3>
                <p class="text-muted-foreground max-w-md mb-8">Keep track of your spending methods by adding your bank accounts, e-wallets, cash, and credit cards to your digital wallet.</p>
                <button 
                    @click="showAddModal = true"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-8 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-lg shadow-primary/25"
                >
                    Add Your First Account
                </button>
            </div>
        </div>

        <AddCardModal :show="showAddModal" @close="showAddModal = false" />

        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Remove Card"
            message="Are you sure you want to remove this card from your wallet? This will not delete your transactions but the card info will be removed."
            confirm-button-text="Remove"
            @confirm="deleteCard"
            @close="showDeleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
