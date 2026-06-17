<script setup>
import { computed, defineAsyncComponent } from 'vue';
const VueApexCharts = defineAsyncComponent(() => import('vue3-apexcharts'));
import { useThemeStore } from '@/Stores/theme';
import { useCurrency } from '@/Composables/useCurrency';

const props = defineProps({
    viewType: {
        type: String,
        required: true,
    },
    chartData: {
        type: Array,
        required: true,
    },
    periodLabel: {
        type: String,
        required: true,
    },
});

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);
const { formatMoney } = useCurrency();

// Chart setup
const chartSeries = computed(() => {
    if (props.viewType === 'monthly') {
        return [{
            name: 'Daily Spending',
            data: props.chartData.map(item => item.amount)
        }];
    } else {
        return [
            {
                name: 'Income',
                data: props.chartData.map(item => item.income)
            },
            {
                name: 'Expenses',
                data: props.chartData.map(item => item.expense)
            }
        ];
    }
});

const chartOptions = computed(() => {
    const isDarkTheme = isDark.value;
    const labelColor = isDarkTheme ? '#9CA3AF' : '#6B7280';
    const gridColor = isDarkTheme ? '#374151' : '#E5E7EB';
    
    if (props.viewType === 'monthly') {
        return {
            chart: {
                type: 'area',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                background: 'transparent',
                toolbar: { show: false },
                animations: { enabled: true }
            },
            colors: ['#7033ff'],
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
                categories: props.chartData.map(item => item.label),
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '11px'
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: labelColor },
                    formatter: (val) => formatMoney(val * 100)
                }
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4
            },
            tooltip: {
                theme: isDarkTheme ? 'dark' : 'light',
                y: {
                    formatter: (val) => formatMoney(val * 100)
                }
            }
        };
    } else {
        return {
            chart: {
                type: 'bar',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                background: 'transparent',
                toolbar: { show: false },
                animations: { enabled: true }
            },
            colors: ['#10b981', '#ef4444'], // Green for income, Red for expenses
            plotOptions: {
                bar: {
                    horizontal: false,
                    columnWidth: '55%',
                    borderRadius: 6
                }
            },
            dataLabels: { enabled: false },
            stroke: {
                show: true,
                width: 2,
                colors: ['transparent']
            },
            xaxis: {
                categories: props.chartData.map(item => item.label),
                labels: {
                    style: {
                        colors: labelColor,
                        fontSize: '12px'
                    }
                },
                axisBorder: { show: false },
                axisTicks: { show: false }
            },
            yaxis: {
                labels: {
                    style: { colors: labelColor },
                    formatter: (val) => formatMoney(val * 100)
                }
            },
            grid: {
                borderColor: gridColor,
                strokeDashArray: 4
            },
            legend: {
                position: 'top',
                horizontalAlign: 'right',
                labels: {
                    colors: isDarkTheme ? '#F9FAFB' : '#111827'
                }
            },
            tooltip: {
                theme: isDarkTheme ? 'dark' : 'light',
                y: {
                    formatter: (val) => formatMoney(val * 100)
                }
            }
        };
    }
});
</script>

<template>
    <div class="lg:col-span-2 glass-card p-6 flex flex-col justify-between shadow-sm print:break-inside-avoid">
        <div>
            <h2 class="text-lg font-semibold text-foreground">
                {{ viewType === 'monthly' ? 'Daily Expense Trend' : 'Monthly Cash Flow' }}
            </h2>
            <p class="text-xs text-muted-foreground mt-0.5">Visual representation of funds during {{ periodLabel }}.</p>
        </div>
        <div class="h-[300px] w-full mt-6">
            <VueApexCharts
                :type="viewType === 'monthly' ? 'area' : 'bar'"
                height="100%"
                :options="chartOptions"
                :series="chartSeries"
            />
        </div>
    </div>
</template>
