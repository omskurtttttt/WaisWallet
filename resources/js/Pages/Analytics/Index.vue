<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
import { computed, defineAsyncComponent } from 'vue';
const VueApexCharts = defineAsyncComponent(() => import('vue3-apexcharts'));
import { useThemeStore } from '@/Stores/theme';
import { useCurrency } from '@/Composables/useCurrency';
import { useChartTheme } from '@/Composables/useChartTheme';

const props = defineProps({
    incomeVsExpenses: Array,
    categoryBreakdown: Array,
    dailySpending: Array,
    currency: String,
});

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);
const { chartColors } = useChartTheme();

const { formatMoney } = useCurrency();

// 1. Income vs Expenses Chart Options & Series
const incomeVsExpensesSeries = computed(() => {
    return [
        {
            name: 'Income',
            data: props.incomeVsExpenses.map(item => item.income),
        },
        {
            name: 'Expenses',
            data: props.incomeVsExpenses.map(item => item.expense),
        }
    ];
});

const incomeVsExpensesOptions = computed(() => ({
    chart: {
        type: 'bar',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        background: 'transparent',
        toolbar: { show: false },
        animations: { enabled: true }
    },
    colors: [chartColors.value.chart1, chartColors.value.destructive], // Green for income, Red for expenses
    plotOptions: {
        bar: {
            horizontal: false,
            columnWidth: '55%',
            borderRadius: 6,
            endingShape: 'rounded'
        },
    },
    dataLabels: { enabled: false },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: props.incomeVsExpenses.map(item => item.month),
        labels: {
            style: {
                colors: chartColors.value.textMuted,
                fontSize: '12px'
            }
        },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        title: {
            text: `Amount (${props.currency})`,
            style: {
                color: chartColors.value.textMuted,
            }
        },
        labels: {
            style: {
                colors: chartColors.value.textMuted,
            },
            formatter: (val) => formatMoney(val * 100, { maximumFractionDigits: 0 })
        }
    },
    grid: {
        borderColor: chartColors.value.border,
        strokeDashArray: 4,
    },
    legend: {
        position: 'top',
        horizontalAlign: 'right',
        labels: {
            colors: chartColors.value.text,
        }
    },
    fill: { opacity: 1 },
    tooltip: {
        theme: isDark.value ? 'dark' : 'light',
        y: {
            formatter: (val) => formatMoney(val * 100, { maximumFractionDigits: 0 })
        }
    }
}));

// 2. Daily Spending Trend Chart Options & Series
const dailySpendingSeries = computed(() => {
    return [{
        name: 'Spending',
        data: props.dailySpending.map(item => item.amount)
    }];
});

const dailySpendingOptions = computed(() => ({
    chart: {
        type: 'area',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        background: 'transparent',
        toolbar: { show: false },
        sparkline: { enabled: false }
    },
    colors: [chartColors.value.chart2],
    dataLabels: { enabled: false },
    stroke: {
        curve: 'smooth',
        width: 3
    },
    fill: {
        type: 'gradient',
        gradient: {
            shadeIntensity: 1,
            opacityFrom: 0.45,
            opacityTo: 0.05,
            stops: [0, 100]
        }
    },
    xaxis: {
        categories: props.dailySpending.map(item => item.date),
        labels: {
            style: {
                colors: chartColors.value.textMuted,
                fontSize: '11px'
            }
        },
        axisBorder: { show: false },
        axisTicks: { show: false }
    },
    yaxis: {
        labels: {
            style: {
                colors: chartColors.value.textMuted,
            },
            formatter: (val) => formatMoney(val * 100, { maximumFractionDigits: 0 })
        }
    },
    grid: {
        borderColor: chartColors.value.border,
        strokeDashArray: 4,
    },
    tooltip: {
        theme: isDark.value ? 'dark' : 'light',
        y: {
            formatter: (val) => formatMoney(val * 100, { maximumFractionDigits: 0 })
        }
    }
}));

// 3. Category Breakdown Chart Options & Series
const hasCategoryData = computed(() => props.categoryBreakdown && props.categoryBreakdown.length > 0);

const categorySeries = computed(() => {
    return hasCategoryData.value ? props.categoryBreakdown.map(item => item.value) : [1];
});

const categoryOptions = computed(() => ({
    chart: {
        type: 'pie',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        background: 'transparent',
        animations: { enabled: true }
    },
    colors: hasCategoryData.value ? props.categoryBreakdown.map(item => item.color) : ['#E5E7EB'],
    labels: hasCategoryData.value ? props.categoryBreakdown.map(item => item.name) : ['No Data'],
    stroke: {
        colors: [chartColors.value.card],
        width: 2
    },
    dataLabels: {
        enabled: true,
        formatter: function (val) {
            return val.toFixed(0) + "%";
        },
        style: {
            fontSize: '12px',
            fontFamily: 'Plus Jakarta Sans, sans-serif',
            fontWeight: 'bold',
        },
        dropShadow: { enabled: false }
    },
    legend: {
        position: 'bottom',
        labels: {
            colors: chartColors.value.textMuted,
        }
    },
    tooltip: {
        theme: isDark.value ? 'dark' : 'light',
        y: {
            formatter: (val) => formatMoney(val * 100, { maximumFractionDigits: 0 })
        }
    }
}));
</script>

<template>
    <Head title="Analytics" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <!-- Header Row -->
            <div class="animate-fade-in">
                <h1 class="text-2xl font-bold text-foreground">Analytics</h1>
                <p class="mt-1 text-sm text-muted-foreground">Analyze your financial trends, balances, and categories.</p>
            </div>

            <!-- Top Row: Income vs Expenses -->
            <div class="glass-card p-6 animate-slide-up">
                <h2 class="text-lg font-semibold text-foreground mb-6">Income vs Expenses (Last 6 Months)</h2>
                <div class="h-[300px] w-full">
                    <VueApexCharts
                        type="bar"
                        height="100%"
                        :options="incomeVsExpensesOptions"
                        :series="incomeVsExpensesSeries"
                    />
                </div>
            </div>

            <!-- Bottom Row: Daily Spending & Category Breakdown -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Daily Spending -->
                <div class="glass-card p-6 animate-slide-up" style="animation-delay: 0.1s">
                    <h2 class="text-lg font-semibold text-foreground mb-6">Daily Expense Trend (Current Month)</h2>
                    <div class="h-[260px] w-full">
                        <VueApexCharts
                            type="area"
                            height="100%"
                            :options="dailySpendingOptions"
                            :series="dailySpendingSeries"
                        />
                    </div>
                </div>

                <!-- Category Breakdown -->
                <div class="glass-card p-6 animate-slide-up" style="animation-delay: 0.2s">
                    <h2 class="text-lg font-semibold text-foreground mb-6">Expense Category Breakdown (Current Month)</h2>
                    
                    <div v-if="hasCategoryData" class="flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="h-[250px] w-full sm:w-[50%] flex justify-center">
                            <VueApexCharts
                                type="pie"
                                height="100%"
                                :options="categoryOptions"
                                :series="categorySeries"
                            />
                        </div>
                        
                        <!-- List view for categories -->
                        <div class="w-full sm:w-[50%] space-y-3">
                            <div 
                                v-for="(item, index) in categoryBreakdown" 
                                :key="index"
                                class="flex items-center justify-between text-sm p-2 rounded-lg hover:bg-muted/30 transition-colors"
                            >
                                <div class="flex items-center gap-2">
                                    <span class="h-3.5 w-3.5 rounded-full" :style="{ backgroundColor: item.color }"></span>
                                    <span class="font-medium text-foreground">{{ item.name }}</span>
                                </div>
                                <span class="font-semibold text-foreground font-financial">{{ formatMoney(item.value * 100, { maximumFractionDigits: 0 }) }}</span>
                            </div>
                        </div>
                    </div>
                    
                    <div v-else class="h-[250px] flex flex-col items-center justify-center text-muted-foreground">
                        <svg class="h-16 w-16 mb-4 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <circle cx="12" cy="12" r="10" />
                            <path d="M12 2a10 10 0 0 1 0 20" />
                        </svg>
                        <p class="text-sm">No expense records found for this month.</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
