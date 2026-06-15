<script setup>
import { computed } from 'vue';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
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

const cards = computed(() => {
    return [
        {
            label: 'Total Income',
            value: formatMoney(props.metrics.income),
            subtext: 'Total received this period',
            colorClass: 'text-chart-1',
            icon: 'income'
        },
        {
            label: 'Total Expenses',
            value: formatMoney(props.metrics.expense),
            subtext: 'Total spent this period',
            colorClass: 'text-[#ff477e]',
            icon: 'expense'
        },
        {
            label: 'Net Savings',
            value: formatMoney(props.metrics.netSavings),
            subtext: 'Net savings this period',
            colorClass: props.metrics.netSavings < 0 ? 'text-destructive' : 'text-primary',
            icon: 'savings'
        },
        {
            label: 'Savings Rate',
            value: `${props.metrics.savingsRate}%`,
            subtext: 'Percentage of income saved',
            colorClass: 'text-[#fdbe2a]',
            icon: 'rate'
        }
    ];
});
</script>

<template>
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 relative z-10">
        <div 
            v-for="(card, index) in cards" 
            :key="index" 
            :class="[
                'glass-card p-5 relative overflow-hidden transition-all duration-200 hover:scale-[1.01] hover:shadow-md flex flex-col justify-between min-h-[148px]', 
                `animate-card-${index + 1}`
            ]"
        >
            <div class="flex flex-col gap-3">
                <!-- Icon at Top Left -->
                <div class="flex items-center min-h-[24px]">
                    <!-- Income/Pie Icon -->
                    <svg v-if="card.icon === 'income'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21.21 15.89A10 10 0 1 1 8 2.83" />
                        <path d="M22 12A10 10 0 0 0 12 2v10z" />
                    </svg>
                    <!-- Expense/Credit Card Icon -->
                    <svg v-else-if="card.icon === 'expense'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2" ry="2" />
                        <line x1="2" y1="10" x2="22" y2="10" />
                    </svg>
                    <!-- Savings/Target Icon -->
                    <svg v-else-if="card.icon === 'savings'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10" />
                        <circle cx="12" cy="12" r="6" />
                        <circle cx="12" cy="12" r="2" />
                    </svg>
                    <!-- Savings Rate/Percent Icon -->
                    <svg v-else-if="card.icon === 'rate'" class="h-5 w-5 text-muted-foreground/50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="19" y1="5" x2="5" y2="19" />
                        <circle cx="6.5" cy="6.5" r="2.5" />
                        <circle cx="17.5" cy="17.5" r="2.5" />
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
                    <span class="text-xs text-muted-foreground/60 leading-none font-sans">{{ card.subtext }}</span>
                </div>
            </div>
        </div>
    </div>
</template>
