<script setup>
import { computed, ref } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useThemeStore } from '@/Stores/theme';
import CustomSelect from '@/Components/CustomSelect.vue';

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

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const hasData = computed(() => props.data && props.data.length > 0);

const period = ref('This month');
const periodOptions = [
    { value: 'This month', label: 'This month' },
    { value: 'Last month', label: 'Last month (Coming Soon)', disabled: true }
];
// ... rest of scripts
const chartOptions = computed(() => ({
    chart: {
        type: 'donut',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        background: 'transparent',
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: { enabled: true, delay: 150 },
            dynamicAnimation: { enabled: true, speed: 350 }
        }
    },
    colors: hasData.value ? props.data.map(item => item.color) : ['#E5E7EB'],
    labels: hasData.value ? props.data.map(item => item.name) : ['No Data'],
    plotOptions: {
        pie: {
            donut: {
                size: '75%',
                labels: {
                    show: true,
                    name: {
                        show: true,
                        fontSize: '13px',
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        fontWeight: 600,
                        color: isDark.value ? '#767e93' : '#6B7280',
                    },
                    value: {
                        show: true,
                        fontSize: '22px',
                        fontFamily: 'Plus Jakarta Sans, sans-serif',
                        fontWeight: 700,
                        color: isDark.value ? '#f0f0f0' : '#111827',
                        formatter: function (val) {
                            return new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: props.currency,
                                maximumFractionDigits: 0
                            }).format(val);
                        }
                    },
                    total: {
                        show: true,
                        showAlways: true,
                        label: 'Total Spent',
                        fontSize: '11px',
                        fontWeight: 600,
                        color: isDark.value ? '#767e93' : '#6B7280',
                        formatter: function (w) {
                            const total = w.globals.seriesTotals.reduce((a, b) => {
                                return a + b
                            }, 0)
                            return new Intl.NumberFormat('en-US', {
                                style: 'currency',
                                currency: props.currency,
                                maximumFractionDigits: 0
                            }).format(total);
                        }
                    }
                }
            }
        }
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        show: true,
        colors: isDark.value ? ['#12131a'] : ['#ffffff'],
        width: 3
    },
    legend: {
        show: false, // We'll build a custom legend below the chart
    },
    tooltip: {
        theme: isDark.value ? 'dark' : 'light',
        y: {
            formatter: function (val) {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: props.currency
                }).format(val);
            }
        }
    }
}));

const series = computed(() => {
    return hasData.value ? props.data.map(item => item.amount) : [1];
});
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between gap-4 mb-6">
            <h2 class="text-lg font-semibold text-foreground whitespace-nowrap">Top Expenses</h2>
            <CustomSelect
                v-model="period"
                :options="periodOptions"
                class="w-36"
                trigger-class="h-9 rounded-lg"
            />
        </div>
        
        <div v-if="hasData" class="flex flex-col items-center">
            <div class="h-[220px] w-full flex justify-center">
                <VueApexCharts
                    type="donut"
                    height="100%"
                    :options="chartOptions"
                    :series="series"
                />
            </div>
            
            <!-- Custom Legend -->
            <div class="mt-4 w-full space-y-2">
                <div v-for="(item, index) in data" :key="index" class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-2">
                        <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: item.color }"></span>
                        <span class="text-muted-foreground">{{ item.name }}</span>
                    </div>
                    <span class="font-medium text-foreground font-financial">
                        {{ new Intl.NumberFormat('en-US', { style: 'currency', currency: currency, maximumFractionDigits: 0 }).format(item.amount) }}
                    </span>
                </div>
            </div>
        </div>
        
        <div v-else class="h-64 flex flex-col items-center justify-center text-muted-foreground">
            <svg class="h-12 w-12 mb-3 opacity-30" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <circle cx="12" cy="12" r="10" />
                <path d="M12 2a10 10 0 0 1 0 20" />
                <circle cx="12" cy="12" r="4" />
            </svg>
            <p class="text-sm">No expenses this month</p>
        </div>
    </div>
</template>
