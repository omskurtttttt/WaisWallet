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
            label: 'Budget Used', 
            value: `${props.metrics.budgetUsedPercentage}%`, 
            subtext: `${formatMoney(props.metrics.monthlySpending, { maximumFractionDigits: 0 })} of ${formatMoney(props.metrics.totalBudgetAllocated, { maximumFractionDigits: 0 })} spent`,
            colorClass: 'text-[#3ee08f]',
            icon: 'income' 
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
            label: 'Total Balance', 
            value: formatMoney(props.metrics.totalBalance), 
            subtext: 'Net asset position',
            colorClass: 'text-[#fdbe2a]',
            icon: 'balance' 
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
                        <div class="flex items-center min-h-[24px]">
                            <!-- Balance/Wallet -> Wallet Icon -->
                            <svg v-if="card.icon === 'balance'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                                <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                                <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                            </svg>
                            <!-- Savings/Diamond -> Target Icon -->
                            <svg v-else-if="card.icon === 'savings'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>
                            <!-- Expense/Activity -> Credit Card Icon -->
                            <svg v-else-if="card.icon === 'expense'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2" ry="2" />
                                <line x1="2" y1="10" x2="22" y2="10" />
                            </svg>
                            <!-- Income/Lightning -> Pie Chart Icon (Colored) -->
                            <svg v-else class="h-5 w-5 text-[#3ee08f] fill-[#3ee08f]/10" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                                <path d="M22 12A10 10 0 0 0 12 2v10z" />
                            </svg>
                        </div>

                        <!-- Big Neon Value -->
                        <div class="mt-1">
                            <p :class="['text-2xl md:text-3xl font-bold font-financial', card.colorClass]">{{ card.value }}</p>
                        </div>
                    </div>

                    <!-- Label and Subtext -->
                    <div class="mt-3 flex flex-col gap-1">
                        <p class="text-[10px] font-bold uppercase tracking-[0.12em] text-muted-foreground/80">{{ card.label }}</p>
                        
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
                            <span class="text-xs text-muted-foreground/60 leading-none font-sans">{{ card.subtext }}</span>
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
