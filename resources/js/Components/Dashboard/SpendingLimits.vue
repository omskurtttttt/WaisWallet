<script setup>
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    currency: {
        type: String,
        default: 'PHP'
    }
});

const emit = defineEmits(['manage']);

const { formatMoney } = useCurrency();
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">Spending Limits</h2>
            <button 
                @click="emit('manage')" 
                class="text-xs font-semibold text-primary hover:text-primary/80 transition-colors duration-200"
            >
                Manage
            </button>
        </div>
        
        <div v-if="data && data.length > 0" class="space-y-4">
            <div 
                v-for="limit in data" 
                :key="limit.id"
                :class="{ 'pb-4 border-b border-border mb-2': limit.is_overall }"
            >
                <div 
                    class="flex justify-between text-sm mb-1.5"
                    :class="{ 'font-semibold text-foreground': limit.is_overall }"
                >
                    <span :class="limit.is_overall ? 'text-foreground' : 'text-muted-foreground'">{{ limit.name }}</span>
                    <span class="font-medium text-foreground font-financial">
                        {{ formatMoney(limit.spent, { maximumFractionDigits: 0 }) }} / {{ formatMoney(limit.allocated, { maximumFractionDigits: 0 }) }}
                    </span>
                </div>
                <div 
                    :class="limit.is_overall ? 'h-3' : 'h-2'"
                    class="w-full rounded-full bg-muted overflow-hidden"
                >
                    <div 
                        :class="[
                            'h-full rounded-full transition-all duration-1000',
                            limit.percentage >= 100 ? 'bg-destructive' : (limit.percentage >= 85 ? 'bg-orange-500' : 'bg-primary')
                        ]" 
                        :style="{ width: `${limit.percentage}%`, backgroundColor: (limit.percentage < 85 && !limit.is_overall) ? limit.color : undefined }"
                    ></div>
                </div>
                <p v-if="limit.percentage >= 100" class="text-xs text-destructive mt-1">
                    Over budget!
                </p>
                <p v-else-if="limit.percentage >= 85" class="text-xs text-orange-500 mt-1">
                    Nearing budget limit.
                </p>
            </div>
        </div>
        
        <div v-else class="text-sm text-muted-foreground text-center py-4">
            No spending limits set.
        </div>
    </div>
</template>
