<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch, computed } from 'vue';
import { useDebounce } from '@/Composables/useDebounce';

const { debounce } = useDebounce();
import AddTransactionModal from './Partials/AddTransactionModal.vue';
import ManageCategoriesModal from './Partials/ManageCategoriesModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useIcons } from '@/Composables/useIcons';
import CustomSelect from '@/Components/CustomSelect.vue';
import Badge from '@/Components/UI/Badge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

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
const statusFilter = ref(props.filters.status || 'all');
const dateFrom = ref(props.filters.date_from || '');
const dateTo = ref(props.filters.date_to || '');
const sortBy = ref(props.filters.sort_by || 'date');
const sortOrder = ref(props.filters.sort_order || 'desc');
const showAddModal = ref(false);
const showManageCategoriesModal = ref(false);
const transactionToEdit = ref(null);

const openAddModal = () => {
    transactionToEdit.value = null;
    showAddModal.value = true;
};

const editTransaction = (tx) => {
    transactionToEdit.value = tx;
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    transactionToEdit.value = null;
};

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
        status: statusFilter.value,
        date_from: dateFrom.value,
        date_to: dateTo.value,
        sort_by: sortBy.value,
        sort_order: sortOrder.value,
    }, { preserveState: true, replace: true });
}, 300);

watch([search, typeFilter, categoryFilter, statusFilter, dateFrom, dateTo, sortBy, sortOrder], applyFilters);

const toggleSort = (field) => {
    if (sortBy.value === field) {
        sortOrder.value = sortOrder.value === 'asc' ? 'desc' : 'asc';
    } else {
        sortBy.value = field;
        sortOrder.value = 'desc';
    }
};

const clearDateRange = () => {
    dateFrom.value = '';
    dateTo.value = '';
};

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

const statusOptions = [
    { value: 'all', label: 'All Statuses' },
    { value: 'completed', label: 'Completed' },
    { value: 'pending', label: 'Pending' },
    { value: 'failed', label: 'Failed' }
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
                <div class="flex flex-col sm:flex-row items-center gap-3 w-full sm:w-auto">
                    <Link
                        :href="route('transactions.recurring.index')"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-sm font-semibold text-foreground transition-all duration-200 hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-sm w-full sm:w-auto"
                    >
                        <svg class="h-4 w-4 shrink-0 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                            <path d="M3 3v5h5"/>
                            <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                            <path d="M16 16h5v5"/>
                        </svg>
                        <span>Recurring Templates</span>
                    </Link>
                    <button
                        type="button"
                        @click="showManageCategoriesModal = true"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-sm font-semibold text-foreground transition-all duration-200 hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-sm w-full sm:w-auto"
                    >
                        <svg class="h-4 w-4 shrink-0 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l6.58-6.58c.94-.94.94-2.48 0-3.42L12 2Z"/>
                            <path d="M7 7h.01"/>
                        </svg>
                        <span>Categories</span>
                    </button>
                    <button 
                        @click="openAddModal"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-md shadow-primary/25 w-full sm:w-auto shrink-0"
                    >
                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Add Transaction</span>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="glass-card p-4 animate-slide-up space-y-4 relative z-20">
                <div class="flex flex-col xl:flex-row gap-4">
                    <!-- Search Input -->
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
                    <!-- Type, Category, Status Selects -->
                    <div class="flex flex-col sm:flex-row gap-3 w-full xl:w-auto">
                        <CustomSelect 
                            v-model="typeFilter"
                            :options="typeOptions"
                            class="w-full sm:min-w-[140px] sm:w-auto"
                        />
                        <CustomSelect 
                            v-model="categoryFilter"
                            :options="categoryOptions"
                            class="w-full sm:min-w-[200px] sm:w-auto"
                        />
                        <CustomSelect 
                            v-model="statusFilter"
                            :options="statusOptions"
                            class="w-full sm:min-w-[150px] sm:w-auto"
                        />
                    </div>
                </div>
                
                <!-- Date Range picker -->
                <div class="flex flex-col sm:flex-row items-start sm:items-center gap-3 pt-3 border-t border-border">
                    <span class="text-xs font-semibold text-muted-foreground uppercase tracking-[0.08em] whitespace-nowrap">Date Range:</span>
                    <div class="flex items-center gap-2 w-full sm:w-auto">
                        <input 
                            v-model="dateFrom"
                            type="date" 
                            class="h-9 w-full sm:w-auto flex-1 sm:flex-none rounded-xl border-0 bg-input px-3 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
                        />
                        <span class="text-muted-foreground text-xs font-semibold">to</span>
                        <input 
                            v-model="dateTo"
                            type="date" 
                            class="h-9 w-full sm:w-auto flex-1 sm:flex-none rounded-xl border-0 bg-input px-3 text-xs text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all"
                        />
                        <button 
                            v-if="dateFrom || dateTo"
                            @click="clearDateRange"
                            class="text-xs text-primary hover:text-primary/80 font-semibold pl-1"
                        >
                            Clear
                        </button>
                    </div>
                </div>
            </div>

            <!-- Transactions Table -->
            <div class="glass-card overflow-hidden animate-slide-up relative z-10" style="animation-delay: 0.1s">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-muted/30 text-muted-foreground border-b border-border">
                            <tr>
                                <th 
                                    @click="toggleSort('name')" 
                                    class="px-6 py-4 font-semibold cursor-pointer hover:text-foreground transition-colors group select-none"
                                >
                                    <div class="flex items-center gap-1">
                                        <span>Transaction</span>
                                        <span class="transition-opacity opacity-0 group-hover:opacity-100 flex items-center" :class="{ 'opacity-100': sortBy === 'name' }">
                                            <svg v-if="sortBy === 'name' && sortOrder === 'asc'" class="h-3 w-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                                            <svg v-else class="h-3 w-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                        </span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 font-semibold">Category</th>
                                <th 
                                    @click="toggleSort('date')" 
                                    class="px-6 py-4 font-semibold cursor-pointer hover:text-foreground transition-colors group select-none"
                                >
                                    <div class="flex items-center gap-1">
                                        <span>Date</span>
                                        <span class="transition-opacity opacity-0 group-hover:opacity-100 flex items-center" :class="{ 'opacity-100': sortBy === 'date' }">
                                            <svg v-if="sortBy === 'date' && sortOrder === 'asc'" class="h-3 w-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                                            <svg v-else class="h-3 w-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                        </span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 font-semibold">Method</th>
                                <th 
                                    @click="toggleSort('amount')" 
                                    class="px-6 py-4 font-semibold cursor-pointer hover:text-foreground transition-colors group select-none text-right"
                                >
                                    <div class="flex items-center justify-end gap-1">
                                        <span>Amount</span>
                                        <span class="transition-opacity opacity-0 group-hover:opacity-100 flex items-center" :class="{ 'opacity-100': sortBy === 'amount' }">
                                            <svg v-if="sortBy === 'amount' && sortOrder === 'asc'" class="h-3 w-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="18 15 12 9 6 15"/></svg>
                                            <svg v-else class="h-3 w-3 text-primary" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="6 9 12 15 18 9"/></svg>
                                        </span>
                                    </div>
                                </th>
                                <th class="px-6 py-4 font-semibold text-right"></th>
                            </tr>
                        </thead>
                        <tbody v-if="transactions.data.length > 0" class="divide-y divide-border">
                            <tr v-for="tx in transactions.data" :key="tx.id" class="hover:bg-muted/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-foreground">{{ tx.title }}</span>
                                        <Badge 
                                            :variant="tx.status === 'completed' ? 'success' : (tx.status === 'failed' ? 'danger' : 'warning')"
                                            size="sm"
                                            class="capitalize"
                                        >
                                            {{ tx.status }}
                                        </Badge>
                                    </div>
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
                                    <Badge v-if="tx.card" variant="secondary" size="md">
                                        {{ tx.card.brand }} •••• {{ tx.card.last_four }}
                                    </Badge>
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
                                    <div class="flex items-center justify-end gap-3">
                                        <button @click="editTransaction(tx)" class="text-muted-foreground hover:text-primary transition-colors" title="Edit Transaction">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </button>
                                        <button @click="confirmDeleteTransaction(tx.id)" class="text-muted-foreground hover:text-destructive transition-colors" title="Delete Transaction">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                        <tbody v-else>
                            <tr>
                                <td colspan="6" class="p-0">
                                    <EmptyState 
                                        title="No transactions found"
                                        description="Try adjusting your filters or add a new transaction."
                                    >
                                        <template #icon>
                                            <svg class="h-12 w-12 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                                                <line x1="16" x2="16" y1="2" y2="6"/>
                                                <line x1="8" x2="8" y1="2" y2="6"/>
                                                <line x1="3" x2="21" y1="10" y2="10"/>
                                            </svg>
                                        </template>
                                    </EmptyState>
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
            :transaction="transactionToEdit"
            @close="closeAddModal" 
        />

        <ManageCategoriesModal
            :show="showManageCategoriesModal"
            :categories="categories"
            @close="showManageCategoriesModal = false"
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
