<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import debounce from 'lodash/debounce';
import AddTransactionModal from './Partials/AddTransactionModal.vue';

const props = defineProps({
    transactions: Object,
    filters: Object,
    categories: Array,
    cards: Array,
    currency: String,
});

const search = ref(props.filters.search || '');
const typeFilter = ref(props.filters.type || 'all');
const categoryFilter = ref(props.filters.category || 'all');
const showAddModal = ref(false);

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

const applyFilters = debounce(() => {
    router.get(route('transactions.index'), {
        search: search.value,
        type: typeFilter.value,
        category: categoryFilter.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([search, typeFilter, categoryFilter], applyFilters);

const deleteTransaction = (id) => {
    if (confirm('Are you sure you want to delete this transaction?')) {
        router.delete(route('transactions.destroy', id), {
            preserveScroll: true
        });
    }
};
</script>

<template>
    <Head title="Transactions" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Transactions</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage and track your income and expenses.</p>
                </div>
                <button 
                    @click="showAddModal = true"
                    class="inline-flex h-11 items-center justify-center rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-md shadow-primary/25"
                >
                    + Add Transaction
                </button>
            </div>

            <!-- Filters -->
            <div class="glass-card p-4 animate-slide-up flex flex-col md:flex-row gap-4">
                <div class="flex-1 relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-5 w-5 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/>
                    </svg>
                    <input 
                        v-model="search"
                        type="text" 
                        placeholder="Search transactions..." 
                        class="h-10 w-full rounded-lg border-0 bg-input pl-10 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
                    />
                </div>
                <div class="flex flex-wrap md:flex-nowrap gap-4">
                    <select 
                        v-model="typeFilter"
                        class="h-10 rounded-lg border-0 bg-input px-4 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all min-w-[140px]"
                    >
                        <option value="all">All Types</option>
                        <option value="income">Income</option>
                        <option value="expense">Expense</option>
                    </select>
                    <select 
                        v-model="categoryFilter"
                        class="h-10 rounded-lg border-0 bg-input px-4 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all min-w-[160px]"
                    >
                        <option value="all">All Categories</option>
                        <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="glass-card overflow-hidden animate-slide-up" style="animation-delay: 0.1s">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-muted/30 text-muted-foreground border-b border-border">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Transaction</th>
                                <th class="px-6 py-4 font-semibold">Category</th>
                                <th class="px-6 py-4 font-semibold">Date</th>
                                <th class="px-6 py-4 font-semibold">Method</th>
                                <th class="px-6 py-4 font-semibold text-right">Amount</th>
                                <th class="px-6 py-4 font-semibold text-right"></th>
                            </tr>
                        </thead>
                        <tbody v-if="transactions.data.length > 0" class="divide-y divide-border">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-muted/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="font-medium text-foreground">{{ tx.title }}</div>
                                    <div class="text-xs text-muted-foreground mt-0.5 truncate max-w-[200px]">{{ tx.notes || 'No notes' }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="tx.category" class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-md" :style="{ backgroundColor: `${tx.category.color}20`, color: tx.category.color }">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(tx.category.icon)"></svg>
                                        </div>
                                        <span>{{ tx.category.name }}</span>
                                    </div>
                                    <span v-else class="text-muted-foreground italic">Uncategorized</span>
                                </td>
                                <td class="px-6 py-4 text-muted-foreground">{{ tx.display_date }}</td>
                                <td class="px-6 py-4">
                                    <span v-if="tx.card" class="inline-flex items-center rounded-md bg-muted px-2 py-1 text-xs font-medium text-muted-foreground">
                                        {{ tx.card.brand }} •••• {{ tx.card.last_four }}
                                    </span>
                                    <span v-else class="text-muted-foreground">Cash</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="[
                                        'font-semibold font-financial',
                                        tx.type === 'income' ? 'text-chart-1' : 'text-foreground'
                                    ]">
                                        {{ tx.type === 'income' ? '+' : '-' }}{{ formatMoney(tx.amount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <button @click="deleteTransaction(tx.id)" class="text-muted-foreground hover:text-destructive transition-colors">
                                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                            <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="px-6 py-12 text-center text-muted-foreground">
                                    <div class="flex flex-col items-center justify-center">
                                        <svg class="h-12 w-12 opacity-20 mb-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                            <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                                            <line x1="16" x2="16" y1="2" y2="6"/>
                                            <line x1="8" x2="8" y1="2" y2="6"/>
                                            <line x1="3" x2="21" y1="10" y2="10"/>
                                        </svg>
                                        <p class="text-base font-medium">No transactions found</p>
                                        <p class="text-sm mt-1">Try adjusting your filters or add a new transaction.</p>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div v-if="transactions.links && transactions.links.length > 3" class="px-6 py-4 border-t border-border flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">
                        Showing {{ transactions.from || 0 }} to {{ transactions.to || 0 }} of {{ transactions.total }} results
                    </span>
                    <div class="flex gap-1">
                        <Component
                            v-for="(link, i) in transactions.links"
                            :key="i"
                            :is="link.url ? Link : 'span'"
                            :href="link.url"
                            v-html="link.label"
                            :class="[
                                'px-3 py-1 text-sm rounded-md transition-colors',
                                link.active ? 'bg-primary text-primary-foreground font-medium' : 'text-foreground hover:bg-muted',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        />
                    </div>
                </div>
            </div>
        </div>

        <AddTransactionModal 
            :show="showAddModal" 
            :categories="categories" 
            :cards="cards" 
            @close="showAddModal = false" 
        />
    </AuthenticatedLayout>
</template>
