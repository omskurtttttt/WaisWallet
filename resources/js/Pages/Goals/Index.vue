<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AddGoalModal from './Partials/AddGoalModal.vue';
import AddFundsModal from './Partials/AddFundsModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';

import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    goals: Array,
    currency: String,
});

const showAddGoalModal = ref(false);
const showAddFundsModal = ref(false);
const selectedGoal = ref(null);

const { formatMoney } = useCurrency();

const openAddFunds = (goal) => {
    selectedGoal.value = goal;
    showAddFundsModal.value = true;
};

const showDeleteConfirm = ref(false);
const goalToDelete = ref(null);

const confirmDeleteGoal = (id) => {
    goalToDelete.value = id;
    showDeleteConfirm.value = true;
};

const deleteGoal = () => {
    if (goalToDelete.value) {
        router.delete(route('goals.destroy', goalToDelete.value), {
            preserveScroll: true,
            onSuccess: () => {
                showDeleteConfirm.value = false;
                goalToDelete.value = null;
            },
            onError: (errors) => {
                console.error(errors);
                alert('Failed to delete savings goal. Please try again.');
            }
        });
    }
};

const getDaysRemaining = (targetDateStr) => {
    if (!targetDateStr) return null;
    const target = new Date(targetDateStr);
    const today = new Date();
    today.setHours(0,0,0,0);
    const diffTime = target - today;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    return diffDays;
};
</script>

<template>
    <Head title="Savings Goals" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Header Row -->
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Savings Goals</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Track your savings targets and milestones.</p>
                </div>
                <button 
                    @click="showAddGoalModal = true"
                    class="inline-flex h-11 items-center justify-center gap-2 rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground hover:bg-primary/90 transition-all duration-200 shadow-md shadow-primary/25"
                >
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14" /><path d="M5 12h14" />
                    </svg>
                    New Goal
                </button>
            </div>

            <!-- Goals Grid -->
            <div v-if="goals && goals.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-slide-up">
                <div 
                    v-for="goal in goals" 
                    :key="goal.id"
                    class="glass-card p-6 flex flex-col justify-between hover:shadow-lg transition-all duration-300"
                >
                    <div>
                        <!-- Top Info -->
                        <div class="flex items-start justify-between mb-4">
                            <div>
                                <h3 class="text-lg font-bold text-foreground">{{ goal.name }}</h3>
                                <p class="text-xs text-muted-foreground mt-0.5">
                                    Target Date: {{ goal.display_date }}
                                </p>
                            </div>
                            
                            <!-- Badges / Days Remaining -->
                            <div class="text-right">
                                <span 
                                    v-if="goal.percentage >= 100" 
                                    class="inline-flex items-center rounded-md bg-chart-1/10 px-2 py-1 text-xs font-semibold text-chart-1"
                                >
                                    Completed 🎉
                                </span>
                                <span 
                                    v-else-if="getDaysRemaining(goal.target_date) !== null"
                                    :class="[
                                        'inline-flex items-center rounded-md px-2 py-1 text-xs font-semibold',
                                        getDaysRemaining(goal.target_date) <= 30 ? 'bg-destructive/10 text-destructive' : 'bg-muted text-muted-foreground'
                                    ]"
                                >
                                    {{ getDaysRemaining(goal.target_date) }} days left
                                </span>
                            </div>
                        </div>

                        <!-- Progress Bar & Target -->
                        <div class="space-y-2 mt-6">
                            <div class="flex justify-between text-sm">
                                <span class="font-medium text-foreground">{{ formatMoney(goal.current_amount, { maximumFractionDigits: 0 }) }}</span>
                                <span class="text-muted-foreground">Target: {{ formatMoney(goal.target_amount, { maximumFractionDigits: 0 }) }}</span>
                            </div>
                            
                            <!-- Progress Container -->
                            <div class="h-3 w-full rounded-full bg-muted overflow-hidden">
                                <div 
                                    :class="[
                                        'h-full rounded-full transition-all duration-1000',
                                        goal.percentage >= 100 ? 'bg-chart-1' : 'bg-primary'
                                    ]" 
                                    :style="{ width: `${goal.percentage}%` }"
                                ></div>
                            </div>

                            <div class="flex justify-between text-xs text-muted-foreground pt-1">
                                <span>{{ goal.percentage }}% saved</span>
                                <span v-if="goal.percentage < 100">{{ formatMoney(goal.remaining, { maximumFractionDigits: 0 }) }} remaining</span>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="flex gap-3 mt-8 pt-4 border-t border-border">
                        <button 
                            @click="openAddFunds(goal)"
                            class="flex-1 inline-flex h-10 items-center justify-center rounded-xl bg-primary/10 text-primary hover:bg-primary/20 text-sm font-semibold transition-all duration-200"
                        >
                            Adjust Funds
                        </button>
                        <button 
                            @click="confirmDeleteGoal(goal.id)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-destructive/10 text-destructive hover:bg-destructive hover:text-destructive-foreground transition-all duration-200"
                            title="Delete Goal"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 6h18"/><path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"/><path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"/>
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div 
                v-else 
                class="glass-card flex flex-col items-center justify-center py-20 px-4 text-center animate-slide-up"
            >
                <div class="h-20 w-20 rounded-full bg-primary/10 text-primary flex items-center justify-center mb-6">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10" />
                        <path d="M8 12.5h8" />
                        <path d="M12 8.5v8" />
                    </svg>
                </div>
                <h3 class="text-xl font-bold text-foreground mb-2">No Savings Goals Yet</h3>
                <p class="text-muted-foreground max-w-md mb-8">
                    Create savings goals to keep track of big purchases, emergency funds, or investment milestones.
                </p>
                <button 
                    @click="showAddGoalModal = true"
                    class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-8 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-lg shadow-primary/25"
                >
                    Add Your First Goal
                </button>
            </div>
        </div>

        <AddGoalModal :show="showAddGoalModal" @close="showAddGoalModal = false" />
        <AddFundsModal :show="showAddFundsModal" :goal="selectedGoal" @close="showAddFundsModal = false" />
        
        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Delete Savings Goal"
            message="Are you sure you want to delete this savings goal? Any saved progress will be lost from this goal tracker (though transaction history is retained)."
            confirm-button-text="Delete"
            @confirm="deleteGoal"
            @close="showDeleteConfirm = false"
        />
    </AuthenticatedLayout>
</template>
