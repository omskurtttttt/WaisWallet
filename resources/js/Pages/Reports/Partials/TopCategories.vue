<script setup>
import { useCurrency } from '@/Composables/useCurrency';
import { useIcons } from '@/Composables/useIcons';

defineProps({
    topCategories: {
        type: Array,
        required: true,
    },
    periodLabel: {
        type: String,
        required: true,
    },
});

const { formatMoney } = useCurrency();
const { getIconSvg } = useIcons();
</script>

<template>
    <div class="glass-card p-6 flex flex-col justify-between shadow-sm print:break-inside-avoid">
        <div>
            <h2 class="text-lg font-semibold text-foreground">Expense Categories</h2>
            <p class="text-xs text-muted-foreground mt-0.5">Where your money went in {{ periodLabel }}.</p>
        </div>
        
        <div v-if="topCategories.length > 0" class="mt-6 flex-1 space-y-4">
            <div v-for="(category, index) in topCategories.slice(0, 5)" :key="index" class="space-y-1.5">
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <div class="flex h-7 w-7 items-center justify-center rounded-lg" :style="{ backgroundColor: `${category.color}15`, color: category.color }">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" v-html="getIconSvg(category.icon)"></svg>
                        </div>
                        <span class="font-medium text-foreground">{{ category.name }}</span>
                    </div>
                    <div class="text-right">
                        <span class="font-semibold text-foreground font-financial">{{ formatMoney(category.amount) }}</span>
                        <span class="text-xs text-muted-foreground block font-light">{{ category.percentage }}%</span>
                    </div>
                </div>
                <!-- Progress indicator -->
                <div class="h-1.5 w-full bg-muted rounded-full overflow-hidden">
                    <div class="h-full rounded-full transition-all duration-500" :style="{ width: `${category.percentage}%`, backgroundColor: category.color }"></div>
                </div>
            </div>
            <div v-if="topCategories.length > 5" class="text-center pt-2">
                <p class="text-xs text-muted-foreground">+ {{ topCategories.length - 5 }} other categories</p>
            </div>
        </div>

        <div v-else class="mt-6 flex-1 flex flex-col items-center justify-center text-muted-foreground py-8">
            <svg class="h-12 w-12 opacity-20 mb-3" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/>
                <line x1="16" x2="16" y1="2" y2="6"/>
                <line x1="8" x2="8" y1="2" y2="6"/>
            </svg>
            <p class="text-sm">No expenses recorded</p>
            <p class="text-xs mt-1">There are no expenses in this period.</p>
        </div>
    </div>
</template>
