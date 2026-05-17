<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    metrics: Object,
    currency: String,
});

// Format monetary values
const formatMoney = (amountInCents) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency || 'USD',
        minimumFractionDigits: 2,
    }).format((amountInCents || 0) / 100);
};

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
                <!-- Income Chart Placeholder -->
                <div class="glass-card p-5 lg:col-span-2 animate-slide-up">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-foreground">Total Income</h2>
                        <select class="h-9 rounded-lg border-0 bg-input px-3 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option>This month</option>
                            <option>Last 3 months</option>
                            <option>Last 6 months</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="h-64 flex items-center justify-center text-muted-foreground">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 mb-3 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <rect x="3" y="12" width="4" height="9" rx="0.5" />
                                <rect x="10" y="8" width="4" height="13" rx="0.5" />
                                <rect x="17" y="4" width="4" height="17" rx="0.5" />
                            </svg>
                            <p class="text-sm">Chart will render here</p>
                        </div>
                    </div>
                </div>

                <!-- Budget Chart Placeholder -->
                <div class="glass-card p-5 animate-slide-up">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-foreground">Budget</h2>
                        <select class="h-9 rounded-lg border-0 bg-input px-3 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                            <option>This month</option>
                            <option>Last month</option>
                            <option>This year</option>
                        </select>
                    </div>
                    <div class="h-64 flex items-center justify-center text-muted-foreground">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 mb-3 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 2a10 10 0 0 1 0 20" />
                                <circle cx="12" cy="12" r="4" />
                            </svg>
                            <p class="text-sm">Chart will render here</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Bottom Row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <!-- Recent Transactions Placeholder -->
                <div class="glass-card p-5 lg:col-span-2 animate-slide-up">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-lg font-semibold text-foreground">Recent Transactions</h2>
                        <a href="/transactions" class="text-sm font-medium text-primary hover:text-primary/80 transition-colors">See all →</a>
                    </div>
                    <div class="space-y-3">
                        <div v-for="i in 5" :key="i" class="flex items-center gap-4 p-3 rounded-xl hover:bg-muted/50 transition-colors">
                            <div class="h-10 w-10 rounded-full skeleton shrink-0"></div>
                            <div class="flex-1 min-w-0">
                                <div class="h-4 w-28 skeleton mb-1.5"></div>
                                <div class="h-3 w-20 skeleton"></div>
                            </div>
                            <div class="text-right">
                                <div class="h-4 w-20 skeleton mb-1.5 ml-auto"></div>
                                <div class="h-3 w-14 skeleton ml-auto"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Right Column -->
                <div class="space-y-4">
                    <!-- Spending Limits Placeholder -->
                    <div class="glass-card p-5 animate-slide-up">
                        <h2 class="text-lg font-semibold text-foreground mb-4">Spending Limits</h2>
                        <div class="space-y-3">
                            <div>
                                <div class="flex justify-between text-sm mb-1.5">
                                    <span class="text-muted-foreground">Monthly Spending</span>
                                    <span class="font-medium text-foreground font-financial">$3,280 / $5,000</span>
                                </div>
                                <div class="h-2.5 w-full rounded-full bg-muted overflow-hidden">
                                    <div class="h-full rounded-full bg-primary transition-all duration-1000" style="width: 65.6%"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- My Cards Placeholder -->
                    <div class="glass-card p-5 animate-slide-up">
                        <div class="flex items-center justify-between mb-4">
                            <h2 class="text-lg font-semibold text-foreground">My Cards</h2>
                            <button class="text-sm font-medium text-primary hover:text-primary/80 transition-colors">+ Add</button>
                        </div>
                        <!-- Card Preview -->
                        <div class="relative h-44 rounded-2xl bg-gradient-to-br from-primary via-primary/90 to-chart-4 p-5 text-primary-foreground overflow-hidden">
                            <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -translate-y-1/2 translate-x-1/2"></div>
                            <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-white/5 translate-y-1/2 -translate-x-1/2"></div>
                            <div class="relative z-10 flex flex-col justify-between h-full">
                                <div class="flex justify-between items-start">
                                    <svg class="h-8 w-8 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                        <rect x="1" y="4" width="22" height="16" rx="2" />
                                        <line x1="1" y1="10" x2="23" y2="10" />
                                    </svg>
                                    <span class="text-sm font-medium opacity-80">VISA</span>
                                </div>
                                <div>
                                    <p class="text-lg font-financial tracking-widest mb-2">•••• •••• •••• 4532</p>
                                    <div class="flex justify-between items-end">
                                        <div>
                                            <p class="text-[10px] uppercase opacity-60">Card Holder</p>
                                            <p class="text-sm font-medium">John Doe</p>
                                        </div>
                                        <div>
                                            <p class="text-[10px] uppercase opacity-60">Expires</p>
                                            <p class="text-sm font-financial">12/28</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
