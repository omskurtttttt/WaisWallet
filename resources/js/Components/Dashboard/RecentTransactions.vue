<script setup>
import { Link } from '@inertiajs/vue3';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    currency: {
        type: String,
        default: 'USD'
    }
});

// Format monetary values
const formatMoney = (amountInCents) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency || 'USD',
    }).format((amountInCents || 0) / 100);
};

// Map backend icon strings to SVG paths (simplified mapping)
const getIconSvg = (iconName) => {
    switch (iconName) {
        case 'shopping-bag':
            return '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>';
        case 'home':
            return '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>';
        case 'car':
            return '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>';
        case 'utensils':
            return '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>';
        case 'briefcase':
            return '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>';
        case 'heart-pulse':
            return '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>';
        case 'graduation-cap':
            return '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>';
        default:
            // Fallback dollar sign
            return '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>';
    }
};
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
