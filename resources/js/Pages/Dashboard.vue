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
            change: '+0.0%', // Placeholder until trend is calculated
            positive: props.metrics.totalBalance >= 0, 
            icon: 'balance' 
        },
        { 
            label: 'Monthly Spending', 
            value: formatMoney(props.metrics.monthlySpending), 
            change: `${props.metrics.spendingTrend > 0 ? '+' : ''}${props.metrics.spendingTrend}%`, 
            positive: props.metrics.spendingTrend <= 0, // Less spending is positive
            icon: 'expense' 
        },
        { 
            label: 'Total Savings', 
            value: formatMoney(props.metrics.totalSavings), 
            change: '+0.0%', // Placeholder
            positive: true, 
            icon: 'savings' 
        },
        { 
            label: 'Budget Used', 
            value: `${props.metrics.budgetUsedPercentage}%`, 
            change: '', // Not needed for budget used
            positive: props.metrics.budgetUsedPercentage < 90, 
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
                <div v-for="(card, index) in cards" :key="index" :class="['glass-card p-5 transition-all duration-200 hover:shadow-lg', `animate-card-${index + 1}`]">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted-foreground">{{ card.label }}</p>
                        <div :class="[
                            'flex h-10 w-10 items-center justify-center rounded-xl',
                            card.positive ? 'bg-chart-1/10 text-chart-1' : 'bg-destructive/10 text-destructive'
                        ]">
                            <!-- Balance -->
                            <svg v-if="card.icon === 'balance'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                                <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                            </svg>
                            <!-- Income / Budget -->
                            <svg v-else-if="card.icon === 'income'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 2v20" /><path d="m17 5-5-3-5 3" />
                            </svg>
                            <!-- Expense -->
                            <svg v-else-if="card.icon === 'expense'" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M12 22V2" /><path d="m7 19 5 3 5-3" />
                            </svg>
                            <!-- Savings -->
                            <svg v-else class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M19 5c-1.5 0-2.8 1.4-3 2-3.5-1.5-11-.3-11 5 0 1.8 0 3 2 4.5V20h4v-2h3v2h4v-4c1-.5 1.7-1 2-2h2v-4h-2c0-1-.5-1.5-1-2V5z" />
                                <path d="M2 9v1c0 1.1.9 2 2 2h1" />
                                <path d="M16 11h.01" />
                            </svg>
                        </div>
                    </div>
                    <p class="mt-3 text-2xl font-bold text-foreground font-financial">{{ card.value }}</p>
                    <div v-if="card.change" class="mt-2 flex items-center gap-1.5">
                        <span :class="[
                            'inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium',
                            card.positive ? 'bg-chart-1/10 text-chart-1' : 'bg-destructive/10 text-destructive'
                        ]">
                            <svg v-if="card.positive" class="mr-0.5 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m18 15-6-6-6 6" />
                            </svg>
                            <svg v-else class="mr-0.5 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m6 9 6 6 6-6" />
                            </svg>
                            {{ card.change }}
                        </span>
                        <span class="text-xs text-muted-foreground">vs last month</span>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
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
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
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
