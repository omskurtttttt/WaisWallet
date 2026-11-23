<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AddGoalModal from './Partials/AddGoalModal.vue';
import AddFundsModal from './Partials/AddFundsModal.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import Badge from '@/Components/UI/Badge.vue';
import EmptyState from '@/Components/UI/EmptyState.vue';

import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    goals: Array,
    cards: {
        type: Array,
        default: () => [],
    },
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

// Tabs state
const activeTab = ref('active');

const activeGoals = computed(() => {
    return props.goals.filter(goal => goal.percentage < 100);
});

const completedGoals = computed(() => {
    return props.goals.filter(goal => goal.percentage >= 100);
});

const filteredGoals = computed(() => {
    return activeTab.value === 'active' ? activeGoals.value : completedGoals.value;
});

// Celebration state
const showCelebration = ref(false);
const celebratedGoalName = ref('');

const markGoalComplete = (goal) => {
    router.patch(route('goals.complete', goal.id), {}, {
        preserveScroll: true,
        onSuccess: () => {
            celebratedGoalName.value = goal.name;
            showCelebration.value = true;
            // auto hide celebration after 4 seconds
            setTimeout(() => {
                showCelebration.value = false;
            }, 4000);
        }
    });
};

const getConfettiStyle = (i) => {
    const colors = ['#4ac885', '#7033ff', '#fd822b', '#3276e4', '#fdbe2a', '#e54b4f'];
    const color = colors[i % colors.length];
    const left = `${(i * 8.5) % 100}%`;
    const delay = `${(i * 0.25) % 2}s`;
    const duration = `${2 + (i * 0.2) % 1.5}s`;
    return {
        backgroundColor: color,
        left: left,
        animationDelay: delay,
        animationDuration: duration,
    };
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
                    <svg class="h-3.5 w-3.5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19"></line>
                        <line x1="5" y1="12" x2="19" y2="12"></line>
                    </svg>
                    <span>New Goal</span>
                </button>
            </div>

            <!-- Tabs / Filter -->
            <div class="flex flex-wrap sm:flex-nowrap border-b border-border">
                <button 
                    @click="activeTab = 'active'"
                    :class="[
                        'flex-1 sm:flex-none px-3 sm:px-6 py-3 text-sm font-semibold transition-all duration-200 border-b-2 -mb-[2px] text-center',
                        activeTab === 'active' 
                            ? 'border-primary text-primary' 
                            : 'border-transparent text-muted-foreground hover:text-foreground'
                    ]"
                >
                    Active Goals ({{ activeGoals.length }})
                </button>
                <button 
                    @click="activeTab = 'completed'"
                    :class="[
                        'flex-1 sm:flex-none px-3 sm:px-6 py-3 text-sm font-semibold transition-all duration-200 border-b-2 -mb-[2px] text-center',
                        activeTab === 'completed' 
                            ? 'border-primary text-primary' 
                            : 'border-transparent text-muted-foreground hover:text-foreground'
                    ]"
                >
                    Completed & Archived ({{ completedGoals.length }})
                </button>
            </div>

            <!-- Goals Grid -->
            <div v-if="filteredGoals && filteredGoals.length > 0" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 animate-slide-up">
                <div 
                    v-for="goal in filteredGoals" 
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
                            <div class="text-right flex gap-1 justify-end">
                                <Badge 
                                    v-if="goal.percentage >= 100" 
                                    variant="success"
                                >
                                    Completed 🎉
                                </Badge>
                                <Badge 
                                    v-else-if="getDaysRemaining(goal.target_date) !== null"
                                    :variant="getDaysRemaining(goal.target_date) <= 30 ? 'danger' : 'secondary'"
                                >
                                    {{ getDaysRemaining(goal.target_date) }} days left
                                </Badge>
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
                            v-if="goal.percentage < 100"
                            @click="markGoalComplete(goal)"
                            class="inline-flex h-10 w-10 items-center justify-center rounded-xl bg-chart-1/10 text-chart-1 hover:bg-chart-1 hover:text-white transition-all duration-200"
                            title="Mark as Complete"
                        >
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <polyline points="20 6 9 17 4 12"/>
                            </svg>
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
            <EmptyState 
                v-else
                class="glass-card py-20"
                title="No Savings Goals Yet"
                description="Create savings goals to keep track of big purchases, emergency funds, or investment milestones."
            >
                <template #icon>
                    <div class="h-20 w-20 rounded-full bg-primary/10 text-primary flex items-center justify-center mx-auto mb-6">
                        <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M8 12.5h8" />
                            <path d="M12 8.5v8" />
                        </svg>
                    </div>
                </template>
                <template #action>
                    <button 
                        @click="showAddGoalModal = true"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-8 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90 focus:outline-none focus:ring-2 focus:ring-primary/30 shadow-lg shadow-primary/25"
                    >
                        Add Your First Goal
                    </button>
                </template>
            </EmptyState>
        </div>

        <AddGoalModal :show="showAddGoalModal" @close="showAddGoalModal = false" />
        <AddFundsModal :show="showAddFundsModal" :goal="selectedGoal" :cards="cards" @close="showAddFundsModal = false" />
        
        <ConfirmationModal
            :show="showDeleteConfirm"
            title="Delete Savings Goal"
            message="Are you sure you want to delete this savings goal? Any saved progress will be lost from this goal tracker (though transaction history is retained)."
            confirm-button-text="Delete"
            @confirm="deleteGoal"
            @close="showDeleteConfirm = false"
        />

        <!-- Celebration Overlay -->
        <div 
            v-if="showCelebration" 
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 backdrop-blur-sm animate-fade-in"
        >
            <div class="bg-card text-foreground rounded-2xl p-8 max-w-sm w-full mx-4 text-center shadow-2xl border border-border animate-scale-in relative overflow-hidden">
                <!-- Confetti pieces -->
                <div class="absolute inset-0 pointer-events-none">
                    <div 
                        v-for="i in 15" 
                        :key="i" 
                        class="confetti-piece" 
                        :style="getConfettiStyle(i)"
                    ></div>
                </div>

                <div class="h-20 w-20 mx-auto rounded-full bg-chart-1/10 text-chart-1 flex items-center justify-center mb-6 animate-bounce">
                    <svg class="h-10 w-10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                        <polyline points="20 6 9 17 4 12"/>
                    </svg>
                </div>
                
                <h3 class="text-2xl font-bold mb-2">Goal Completed! 🎉</h3>
                <p class="text-muted-foreground mb-6 text-sm leading-relaxed">
                    Awesome job! You have successfully reached your target for <span class="font-bold text-foreground">{{ celebratedGoalName }}</span>.
                </p>
                
                <button 
                    @click="showCelebration = false"
                    class="w-full inline-flex h-11 items-center justify-center rounded-xl bg-primary px-6 text-sm font-semibold text-primary-foreground transition-all duration-200 hover:bg-primary/90"
                >
                    Awesome!
                </button>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fall {
    0% { transform: translateY(-100px) rotate(0deg); opacity: 1; }
    100% { transform: translateY(400px) rotate(360deg); opacity: 0; }
}

.confetti-piece {
    position: absolute;
    width: 8px;
    height: 16px;
    top: 0;
    animation: fall 3s linear infinite;
    border-radius: 2px;
}

@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

@keyframes scaleIn {
    from { transform: scale(0.9); opacity: 0; }
    to { transform: scale(1); opacity: 1; }
}

.animate-fade-in {
    animation: fadeIn 0.3s ease-out forwards;
}

.animate-scale-in {
    animation: scaleIn 0.3s cubic-bezier(0.34, 1.56, 0.64, 1) forwards;
}
</style>
