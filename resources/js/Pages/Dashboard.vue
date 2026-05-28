<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import IncomeChart from '@/Components/Dashboard/IncomeChart.vue';
import BudgetChart from '@/Components/Dashboard/BudgetChart.vue';
import SpendingLimits from '@/Components/Dashboard/SpendingLimits.vue';
import RecentTransactions from '@/Components/Dashboard/RecentTransactions.vue';
import MyCards from '@/Components/Dashboard/MyCards.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    metrics: Object,
    incomeChartData: Array,
    budgetChartData: Array,
    spendingLimitsData: Array,
    recentTransactionsData: Array,
    myCardsData: Array,
    currency: String,
});

import { useCurrency } from '@/Composables/useCurrency';

const { formatMoney } = useCurrency();

// Map metrics to card structure
const cards = computed(() => {
    return [
        { 
            label: 'Total Balance', 
            value: formatMoney(props.metrics.totalBalance), 
            subtext: 'Net asset position',
            colorClass: 'text-[#fdbe2a]',
            icon: 'balance' 
        },
        { 
            label: 'Total Savings', 
            value: formatMoney(props.metrics.totalSavings), 
            subtext: 'Accumulated goal savings',
            colorClass: 'text-[#8c5cff]',
            icon: 'savings' 
        },
        { 
            label: 'Monthly Spending', 
            value: formatMoney(props.metrics.monthlySpending), 
            change: `${props.metrics.spendingTrend > 0 ? '+' : ''}${props.metrics.spendingTrend}%`, 
            positive: props.metrics.spendingTrend <= 0, 
            subtext: 'Expenses this month',
            colorClass: 'text-[#ff477e]',
            icon: 'expense' 
        },
        { 
            label: 'Budget Used', 
            value: `${props.metrics.budgetUsedPercentage}%`, 
            subtext: `${formatMoney(props.metrics.monthlySpending, { maximumFractionDigits: 0 })} of ${formatMoney(props.metrics.totalBudgetAllocated, { maximumFractionDigits: 0 })} spent`,
            colorClass: 'text-[#3ee08f]',
            icon: 'income' 
        },
    ];
});
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Page Title -->
            <div class="animate-fade-in">
                <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
                <p class="mt-1 text-sm text-muted-foreground">Welcome back! Here's your financial overview.</p>
            </div>

            <!-- Key Metrics Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
                <div v-for="(card, index) in cards" :key="index" :class="['glass-card p-5 relative overflow-hidden transition-all duration-200 hover:scale-[1.01] hover:shadow-md flex flex-col justify-between min-h-[148px]', `animate-card-${index + 1}`]">
                    <div class="flex flex-col gap-3">
                        <!-- Icon at Top Left -->
                        <div class="flex items-center">
                            <div :class="['flex h-7 w-7 items-center justify-center rounded-lg bg-card border border-border shadow-sm', card.colorClass]">
                                <!-- Balance/Wallet -->
                                <svg v-if="card.icon === 'balance'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <line x1="12" y1="1" x2="12" y2="23"></line>
                                    <path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"></path>
                                </svg>
                                <!-- Savings/Diamond -->
                                <svg v-else-if="card.icon === 'savings'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M2.7 10.3a2.4 2.4 0 0 0 0 3.4l7.6 7.6a2.4 2.4 0 0 0 3.4 0l7.6-7.6a2.4 2.4 0 0 0 0-3.4L13.7 2.7a2.4 2.4 0 0 0-3.4 0z"/>
                                </svg>
                                <!-- Expense/Activity -->
                                <svg v-else-if="card.icon === 'expense'" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"></polyline>
                                </svg>
                                <!-- Income/Lightning -->
                                <svg v-else class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                                    <polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"></polygon>
                                </svg>
                            </div>
                        </div>

                        <!-- Big Neon Value -->
                        <div class="mt-1">
                            <p :class="['text-3xl font-extrabold tracking-tight font-financial', card.colorClass]">{{ card.value }}</p>
                        </div>
                    </div>

                    <!-- Label and Subtext -->
                    <div class="mt-3 flex flex-col gap-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.08em] text-muted-foreground">{{ card.label }}</p>
                        
                        <div class="flex items-center gap-1.5 min-h-[16px]">
                            <!-- Spending Trend badge if available -->
                            <span v-if="card.change" :class=" [
                                'inline-flex items-center rounded-md px-1.5 py-0.5 text-[10px] font-semibold leading-none',
                                card.positive ? 'bg-chart-1/10 text-chart-1' : 'bg-destructive/10 text-destructive'
                            ]">
                                <svg v-if="card.positive" class="mr-0.5 h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m6 9 6 6 6-6" />
                                </svg>
                                <svg v-else class="mr-0.5 h-2.5 w-2.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="m18 15-6-6-6 6" />
                                </svg>
                                {{ card.change }}
                            </span>
                            <span class="text-xs text-muted-foreground/75 leading-none">{{ card.subtext }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 relative z-20">
                <!-- Income Chart -->
                <div class="lg:col-span-2">
                    <IncomeChart :data="incomeChartData" :currency="currency" />
                </div>

                <!-- Budget Chart -->
                <div>
                    <BudgetChart :data="budgetChartData" :currency="currency" />
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 relative z-10">
                <!-- Recent Transactions -->
                <div class="lg:col-span-2">
                    <RecentTransactions :data="recentTransactionsData" :currency="currency" />
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Spending Limits -->
                    <SpendingLimits :data="spendingLimitsData" :currency="currency" />

                    <!-- My Cards -->
                    <MyCards :data="myCardsData" />
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
