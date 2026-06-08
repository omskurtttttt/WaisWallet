<script setup>
import { Link } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    }
});

const { formatMoney } = useCurrency();

const getAccountCategory = (brand) => {
    const brandLower = brand?.toLowerCase();
    if (['gcash', 'maya', 'grabpay', 'coins.ph'].includes(brandLower)) {
        return {
            name: 'E-Wallet',
            color: '#8c5cff', // Violet
            icon: 'wallet',
            badgeClass: 'bg-[#8c5cff]/10 text-[#8c5cff]'
        };
    } else if (['bpi', 'bdo', 'metrobank', 'unionbank'].includes(brandLower)) {
        return {
            name: 'Bank Account',
            color: '#38bdf8', // Blue
            icon: 'bank',
            badgeClass: 'bg-[#38bdf8]/10 text-[#38bdf8]'
        };
    } else if (brandLower === 'cash') {
        return {
            name: 'Cash',
            color: '#3ee08f', // Green
            icon: 'cash',
            badgeClass: 'bg-[#3ee08f]/10 text-[#3ee08f]'
        };
    } else {
        return {
            name: 'Credit Card',
            color: '#ff477e', // Red
            icon: 'card',
            badgeClass: 'bg-[#ff477e]/10 text-[#ff477e]'
        };
    }
};

const categorizedAccounts = computed(() => {
    const categories = {
        'E-Wallet': [],
        'Bank Account': [],
        'Cash': [],
        'Credit Card': []
    };
    
    props.data.forEach(account => {
        const cat = getAccountCategory(account.brand);
        categories[cat.name].push({
            ...account,
            catInfo: cat
        });
    });
    
    return Object.keys(categories)
        .map(name => ({
            name,
            accounts: categories[name]
        }))
        .filter(cat => cat.accounts.length > 0);
});
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">Accounts & Wallets</h2>
            <Link :href="route('wallet.index')" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary/80 transition-colors">
                <span>Manage</span>
            </Link>
        </div>
        
        <div v-if="data && data.length > 0" class="space-y-4">
            <div v-for="category in categorizedAccounts" :key="category.name" class="space-y-2">
                <!-- Category Heading -->
                <p class="text-[10px] font-bold uppercase tracking-wider text-muted-foreground/80">
                    {{ category.name }}s
                </p>
                
                <!-- Accounts List -->
                <div class="space-y-1.5">
                    <div 
                        v-for="account in category.accounts" 
                        :key="account.id" 
                        class="flex items-center justify-between p-3 rounded-xl bg-muted/20 border border-border/50 hover:bg-muted/40 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <!-- Dynamic Colored Icon Container -->
                            <div 
                                class="flex h-9 w-9 items-center justify-center rounded-lg shrink-0"
                                :style="{ backgroundColor: `${account.catInfo.color}15`, color: account.catInfo.color }"
                            >
                                <svg v-if="account.catInfo.icon === 'wallet'" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2" />
                                    <path d="M7 11V7a5 5 0 0 1 10 0v4" />
                                </svg>
                                <svg v-else-if="account.catInfo.icon === 'bank'" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <path d="M3 22V11M21 22V11M2 11L12 2l10 9M10 22v-5h4v5M4 11h16M12 5v2" />
                                </svg>
                                <svg v-else-if="account.catInfo.icon === 'cash'" class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect x="2" y="6" width="20" height="12" rx="2" />
                                    <circle cx="12" cy="12" r="2" />
                                    <path d="M6 12h.01M18 12h.01" />
                                </svg>
                                <svg v-else class="h-4.5 w-4.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                                    <rect x="2" y="5" width="20" height="14" rx="2" />
                                    <line x1="2" y1="10" x2="22" y2="10" />
                                </svg>
                            </div>
                            
                            <div>
                                <p class="text-[13px] font-semibold text-foreground leading-tight">{{ account.name }}</p>
                                <p class="text-[10px] text-muted-foreground mt-0.5 uppercase tracking-wider">
                                    {{ account.brand }} <span v-if="account.last_four !== '0000'">• {{ account.last_four }}</span>
                                </p>
                            </div>
                        </div>
                        
                        <!-- Dynamic Balance -->
                        <div class="text-right">
                            <p class="text-sm font-bold font-financial text-foreground">
                                {{ formatMoney(account.balance) }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <div v-else class="h-44 rounded-2xl border-2 border-dashed border-border flex flex-col items-center justify-center text-muted-foreground bg-muted/10">
            <svg class="h-8 w-8 mb-2 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
            </svg>
            <p class="text-sm font-medium mb-1">No accounts added</p>
            <Link :href="route('wallet.index')" class="text-xs text-primary hover:underline">Add your first account</Link>
        </div>
    </div>
</template>
