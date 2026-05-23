<script setup>
import { Link } from '@inertiajs/vue3';
import { useCurrency } from '@/Composables/useCurrency';
import { useIcons } from '@/Composables/useIcons';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    currency: {
        type: String,
        default: 'USD',
    },
});

const { formatMoney } = useCurrency();
const { getIconSvg } = useIcons();
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">Recent Transactions</h2>
            <Link :href="route('transactions.index')" class="text-sm font-medium text-primary hover:text-primary/80 transition-colors">See all →</Link>
        </div>
        
        <div v-if="data && data.length > 0" class="space-y-3">
            <div 
                v-for="transaction in data" 
                :key="transaction.id" 
                class="flex items-center gap-4 p-3 rounded-xl hover:bg-muted/50 transition-colors"
            >
                <!-- Category Icon -->
                <div 
                    class="flex h-10 w-10 items-center justify-center rounded-full shrink-0"
                    :style="{ 
                        backgroundColor: transaction.category ? `${transaction.category.color}20` : '#E5E7EB',
                        color: transaction.category ? transaction.category.color : '#6B7280'
                    }"
                >
                    <svg 
                        v-if="transaction.category"
                        class="h-5 w-5" 
                        viewBox="0 0 24 24" 
                        fill="none" 
                        stroke="currentColor" 
                        stroke-width="2" 
                        stroke-linecap="round" 
                        stroke-linejoin="round"
                        v-html="getIconSvg(transaction.category.icon)"
                    ></svg>
                    <span v-else class="text-sm font-bold">?</span>
                </div>
                
                <!-- Details -->
                <div class="flex-1 min-w-0">
                    <p class="truncate text-sm font-semibold text-foreground">
                        {{ transaction.title }}
                    </p>
                    <p class="truncate text-xs text-muted-foreground mt-0.5">
                        {{ transaction.category ? transaction.category.name : 'Uncategorized' }}
                        <span v-if="transaction.card" class="mx-1">•</span>
                        <span v-if="transaction.card">💳 **{{ transaction.card.last_four }}</span>
                    </p>
                </div>
                
                <!-- Amount -->
                <div class="text-right">
                    <p :class="[
                        'text-sm font-semibold font-financial',
                        transaction.type === 'income' ? 'text-chart-1' : 'text-foreground'
                    ]">
                        {{ transaction.type === 'income' ? '+' : '-' }}{{ formatMoney(transaction.amount) }}
                    </p>
                    <p class="text-xs text-muted-foreground mt-0.5">{{ transaction.date }}</p>
                </div>
            </div>
        </div>
        
        <div v-else class="py-8 text-center text-muted-foreground">
            <svg class="mx-auto h-12 w-12 opacity-30 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                <line x1="16" x2="16" y1="2" y2="6"/>
                <line x1="8" x2="8" y1="2" y2="6"/>
                <line x1="3" x2="21" y1="10" y2="10"/>
                <path d="M8 14h.01"/><path d="M12 14h.01"/><path d="M16 14h.01"/><path d="M8 18h.01"/><path d="M12 18h.01"/><path d="M16 18h.01"/>
            </svg>
            <p class="text-sm">No recent transactions</p>
        </div>
    </div>
</template>
