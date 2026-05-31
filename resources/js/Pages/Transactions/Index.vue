<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useDebounce } from '@/Composables/useDebounce';

const { debounce } = useDebounce();
import AddTransactionModal from './Partials/AddTransactionModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useIcons } from '@/Composables/useIcons';
import CustomSelect from '@/Components/CustomSelect.vue';

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

const { formatMoney } = useCurrency();
const { getIconSvg } = useIcons();

const cleanLabel = (label) => {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
};

const applyFilters = debounce(() => {
    router.get(route('transactions.index'), {
        search: search.value,
        type: typeFilter.value,
        category: categoryFilter.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([search, typeFilter, categoryFilter], applyFilters);

const showDeleteConfirm = ref(false);
const transactionToDelete = ref(null);

const confirmDeleteTransaction = (id) => {
    transactionToDelete.value = id;
    showDeleteConfirm.value = true;
};

const deleteTransaction = () => {
    if (transactionToDelete.value) {
        router.delete(route('transactions.destroy', transactionToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteConfirm.value = false;
                transactionToDelete.value = null;
            },
            onError: (errors) => {
                console.error(errors);
                alert('Failed to delete transaction. Please try again.');
            }
        });
    }
};

const typeOptions = [
    { value: 'all', label: 'All Types' },
    { value: 'income', label: 'Income' },
    { value: 'expense', label: 'Expense' }
];

const categoryOptions = computed(() => {
    return [
        { value: 'all', label: 'All Categories' },
        ...props.categories.map(cat => ({ value: cat.id, label: cat.name }))
    ];
});
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
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-md shadow-primary/25"
                >
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>Add Transaction</span>
                </button>
            </div>

            <!-- Filters -->
            <div class="glass-card p-4 animate-slide-up flex flex-col md:flex-row gap-4 relative z-20">
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
                    <CustomSelect 
                        v-model="typeFilter"
                        :options="typeOptions"
                        class="min-w-[140px]"
                    />
                    <CustomSelect 
                        v-model="categoryFilter"
                        :options="categoryOptions"
                        class="min-w-[160px]"
                    />
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="glass-card overflow-hidden animate-slide-up relative z-10" style="animation-delay: 0.1s">
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
                                    <button @click="confirmDeleteTransaction(tx.id)" class="text-muted-foreground hover:text-destructive transition-colors">
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
                            :class="[
                                'px-3 py-1 text-sm rounded-md transition-colors',
                                link.active ? 'bg-primary text-primary-foreground font-medium' : 'text-foreground hover:bg-muted',
                                !link.url ? 'opacity-50 cursor-not-allowed' : ''
                            ]"
                        >
                            {{ cleanLabel(link.label) }}
                        </Component>
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

        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Delete Transaction"
            message="Are you sure you want to delete this transaction? This action cannot be undone."
            confirm-button-text="Delete"
            @confirm="deleteTransaction"
            @close="showDeleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
