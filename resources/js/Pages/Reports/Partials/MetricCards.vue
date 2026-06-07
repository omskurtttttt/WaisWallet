<script setup>
import { useCurrency } from '@/Composables/useCurrency';

defineProps({
    metrics: {
        type: Object,
        required: true,
    },
    prevPeriodLabel: {
        type: String,
        required: true,
    },
});

const { formatMoney } = useCurrency();
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-slide-up relative z-10" style="animation-delay: 0.05s">
        <!-- Total Income -->
        <div class="glass-card p-5 border-l-4 border-chart-1 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Income</p>
            <p class="mt-2 text-2xl font-bold text-foreground font-financial">{{ formatMoney(metrics.income) }}</p>
            <div class="mt-2 flex items-center gap-1.5 print:hidden">
                <span :class="[
                    'inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium',
                    metrics.trends.income >= 0 ? 'bg-chart-1/10 text-chart-1' : 'bg-destructive/10 text-destructive'
                ]">
                    <svg class="mr-0.5 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path v-if="metrics.trends.income >= 0" d="m18 15-6-6-6 6"/><path v-else d="m6 9 6 6 6-6"/>
                    </svg>
                    {{ Math.abs(metrics.trends.income) }}%
                </span>
                <span class="text-xs text-muted-foreground">vs {{ prevPeriodLabel }}</span>
            </div>
        </div>

        <!-- Total Expenses -->
        <div class="glass-card p-5 border-l-4 border-destructive shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Total Expenses</p>
            <p class="mt-2 text-2xl font-bold text-foreground font-financial">{{ formatMoney(metrics.expense) }}</p>
            <div class="mt-2 flex items-center gap-1.5 print:hidden">
                <span :class="[
                    'inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium',
                    metrics.trends.expense <= 0 ? 'bg-chart-1/10 text-chart-1' : 'bg-destructive/10 text-destructive'
                ]">
                    <svg class="mr-0.5 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path v-if="metrics.trends.expense <= 0" d="m6 9 6 6 6-6"/><path v-else d="m18 15-6-6-6 6"/>
                    </svg>
                    {{ Math.abs(metrics.trends.expense) }}%
                </span>
                <span class="text-xs text-muted-foreground">vs {{ prevPeriodLabel }}</span>
            </div>
        </div>

        <!-- Net Savings -->
        <div class="glass-card p-5 border-l-4 border-primary shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Net Savings</p>
            <p class="mt-2 text-2xl font-bold text-foreground font-financial" :class="metrics.netSavings < 0 ? 'text-destructive' : 'text-foreground'">
                {{ formatMoney(metrics.netSavings) }}
            </p>
            <div class="mt-2 flex items-center gap-1.5 print:hidden">
                <span :class="[
                    'inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium',
                    metrics.trends.netSavings >= 0 ? 'bg-chart-1/10 text-chart-1' : 'bg-destructive/10 text-destructive'
                ]">
                    <svg class="mr-0.5 h-3 w-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path v-if="metrics.trends.netSavings >= 0" d="m18 15-6-6-6 6"/><path v-else d="m6 9 6 6 6-6"/>
                    </svg>
                    {{ Math.abs(metrics.trends.netSavings) }}%
                </span>
                <span class="text-xs text-muted-foreground">vs {{ prevPeriodLabel }}</span>
            </div>
        </div>

        <!-- Savings Rate -->
        <div class="glass-card p-5 border-l-4 border-yellow-500 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-muted-foreground">Savings Rate</p>
            <p class="mt-2 text-2xl font-bold text-foreground font-financial">{{ metrics.savingsRate }}%</p>
            <div class="mt-2 flex items-center gap-1.5 print:hidden">
                <span :class="[
                    'inline-flex items-center rounded-md px-1.5 py-0.5 text-xs font-medium',
                    metrics.savingsRate >= 20 ? 'bg-chart-1/10 text-chart-1' : 'bg-yellow-500/10 text-yellow-600'
                ]">
                    {{ metrics.savingsRate >= 20 ? 'Good' : 'Needs Work' }}
                </span>
                <span class="text-xs text-muted-foreground">Target: 20%+</span>
            </div>
        </div>
    </div>
</template>
