<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddRecurringModal from './Partials/AddRecurringModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import { useCurrency } from '@/Composables/useCurrency';
import { useIcons } from '@/Composables/useIcons';
import Badge from '@/Components/UI/Badge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

const props = defineProps({
    recurringTransactions: Object,
    categories: Array,
    cards: Array,
    currency: String,
});

const showAddModal = ref(false);
const recurringToEdit = ref(null);

const openAddModal = () => {
    recurringToEdit.value = null;
    showAddModal.value = true;
};

const editRecurring = (rt) => {
    recurringToEdit.value = rt;
    showAddModal.value = true;
};

const closeAddModal = () => {
    showAddModal.value = false;
    recurringToEdit.value = null;
};

const { formatMoney } = useCurrency();
const { getIconSvg } = useIcons();

const cleanLabel = (label) => {
    return label.replace('&laquo;', '«').replace('&raquo;', '»');
};

const showDeleteConfirm = ref(false);
const recurringToDelete = ref(null);

const confirmDeleteRecurring = (id) => {
    recurringToDelete.value = id;
    showDeleteConfirm.value = true;
};

const deleteRecurring = () => {
    if (recurringToDelete.value) {
        router.delete(route('transactions.recurring.destroy', recurringToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteConfirm.value = false;
                recurringToDelete.value = null;
            },
            onError: (errors) => {
                console.error(errors);
                alert('Failed to delete template. Please try again.');
            }
        });
    }
};

const toggleActiveStatus = (rt) => {
    router.put(route('transactions.recurring.update', rt.id), {
        title: rt.title,
        amount: rt.amount / 100, // convert back to decimal for request validation
        type: rt.type,
        frequency: rt.frequency,
        next_run_date: rt.next_run_date,
        category_id: rt.category ? rt.category.id : '',
        card_id: rt.card ? rt.card.id : '',
        recipient_name: rt.recipient_name,
        notes: rt.notes,
        is_active: !rt.is_active,
    }, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Recurring Transactions" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Breadcrumbs / Header Row -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 animate-fade-in">
                <div>
                    <div class="flex items-center gap-2 text-xs font-semibold text-muted-foreground uppercase tracking-wider mb-1">
                        <Link :href="route('transactions.index')" class="hover:text-foreground transition-colors">Transactions</Link>
                        <span>/</span>
                        <span class="text-foreground">Recurring Templates</span>
                    </div>
                    <h1 class="text-2xl font-bold text-foreground">Recurring Templates</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage automatically generated transactions.</p>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <Link
                        :href="route('transactions.index')"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl border border-border bg-card px-5 text-sm font-semibold text-foreground transition-all duration-200 hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-sm w-full sm:w-auto"
                    >
                        <svg class="h-4 w-4 shrink-0 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="19" y1="12" x2="5" y2="12"></line>
                            <polyline points="12 19 5 12 12 5"></polyline>
                        </svg>
                        <span>Back to Ledger</span>
                    </Link>
                    <button 
                        @click="openAddModal"
                        class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-md shadow-primary/25 w-full sm:w-auto shrink-0"
                    >
                        <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="12" y1="5" x2="12" y2="19"></line>
                            <line x1="5" y1="12" x2="19" y2="12"></line>
                        </svg>
                        <span>Create Template</span>
                    </button>
                </div>
            </div>

            <!-- Templates List -->
            <div class="glass-card overflow-hidden animate-slide-up relative z-10">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-muted/30 text-muted-foreground border-b border-border">
                            <tr>
                                <th class="px-6 py-4 font-semibold">Template Name</th>
                                <th class="px-6 py-4 font-semibold">Category</th>
                                <th class="px-6 py-4 font-semibold">Frequency</th>
                                <th class="px-6 py-4 font-semibold">Schedule</th>
                                <th class="px-6 py-4 font-semibold">Method</th>
                                <th class="px-6 py-4 font-semibold text-right">Amount</th>
                                <th class="px-6 py-4 font-semibold text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody v-if="recurringTransactions.data.length > 0" class="divide-y divide-border">
                            <tr v-for="rt in recurringTransactions.data" :key="rt.id" class="hover:bg-muted/20 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <span class="font-medium text-foreground">{{ rt.title }}</span>
                                        <Badge 
                                            :variant="rt.is_active ? 'success' : 'secondary'"
                                            size="sm"
                                        >
                                            {{ rt.is_active ? 'Active' : 'Inactive' }}
                                        </Badge>
                                    </div>
                                    <div v-if="rt.recipient_name" class="text-xs text-muted-foreground mt-0.5">
                                        To/From: {{ rt.recipient_name }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div v-if="rt.category" class="flex items-center gap-2">
                                        <div class="flex h-6 w-6 items-center justify-center rounded-md" :style="{ backgroundColor: `${rt.category.color}20`, color: rt.category.color }">
                                            <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(rt.category.icon)"></svg>
                                        </div>
                                        <span>{{ rt.category.name }}</span>
                                    </div>
                                    <span v-else class="text-muted-foreground italic">Uncategorized</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="capitalize text-foreground font-medium px-2.5 py-1 bg-input rounded-lg text-xs">
                                        {{ rt.frequency }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-xs text-foreground">Next: <span class="font-medium text-primary">{{ rt.display_next_run }}</span></div>
                                    <div class="text-[10px] text-muted-foreground mt-0.5">Last: {{ rt.display_last_run }}</div>
                                </td>
                                <td class="px-6 py-4">
                                    <Badge v-if="rt.card" variant="secondary" size="md">
                                        {{ rt.card.brand }} •••• {{ rt.card.last_four }}
                                    </Badge>
                                    <span v-else class="text-muted-foreground text-xs">💵 Cash</span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <span :class="[
                                        'font-semibold font-financial',
                                        rt.type === 'income' ? 'text-chart-1' : 'text-foreground'
                                    ]">
                                        {{ rt.type === 'income' ? '+' : '-' }}{{ formatMoney(rt.amount) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-3">
                                        <!-- Toggle Active Switch -->
                                        <button 
                                            @click="toggleActiveStatus(rt)" 
                                            :class="[
                                                'text-xs font-semibold px-2 py-1 rounded transition-colors',
                                                rt.is_active 
                                                    ? 'text-muted-foreground hover:text-warning' 
                                                    : 'text-primary hover:text-primary-foreground hover:bg-primary/20'
                                            ]"
                                            :title="rt.is_active ? 'Pause Template' : 'Resume Template'"
                                        >
                                            {{ rt.is_active ? 'Pause' : 'Activate' }}
                                        </button>
                                        <button @click="editRecurring(rt)" class="text-muted-foreground hover:text-primary transition-colors" title="Edit Template">
                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                                <path d="M12 20h9"/>
                                                <path d="M16.5 3.5a2.12 2.12 0 0 1 3 3L7 19l-4 1 1-4Z"/>
                                            </svg>
                                        </button>
                                        <button @click="confirmDeleteRecurring(rt.id)" class="text-muted-foreground hover:text-destructive transition-colors" title="Delete Template">
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
                                <td colspan="7" class="p-0">
                                    <EmptyState 
                                        title="No recurring templates found"
                                        description="Setup automatic monthly rent, utility bills, or salary entries."
                                    >
                                        <template #icon>
                                            <svg class="h-12 w-12 mx-auto" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1">
                                                <path d="M21 12a9 9 0 0 0-9-9 9.75 9.75 0 0 0-6.74 2.74L3 8"/>
                                                <path d="M3 3v5h5"/>
                                                <path d="M3 12a9 9 0 0 0 9 9 9.75 9.75 0 0 0 6.74-2.74L21 16"/>
                                                <path d="M16 16h5v5"/>
                                            </svg>
                                        </template>
                                    </EmptyState>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="recurringTransactions.links && recurringTransactions.links.length > 3" class="px-6 py-4 border-t border-border flex items-center justify-between">
                    <span class="text-sm text-muted-foreground">
                        Showing {{ recurringTransactions.from || 0 }} to {{ recurringTransactions.to || 0 }} of {{ recurringTransactions.total }} results
                    </span>
                    <div class="flex gap-1">
                        <Component
                            v-for="(link, i) in recurringTransactions.links"
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

        <AddRecurringModal 
            :show="showAddModal" 
            :categories="categories" 
            :cards="cards" 
            :recurringTransaction="recurringToEdit"
            @close="closeAddModal" 
        />

        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Delete Template"
            message="Are you sure you want to delete this recurring transaction template? This will not affect already generated transactions."
            confirm-button-text="Delete"
            @confirm="deleteRecurring"
            @close="showDeleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
