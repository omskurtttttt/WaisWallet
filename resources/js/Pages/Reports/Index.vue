<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useThemeStore } from '@/Stores/theme';

const props = defineProps({
    filters: Object,
    metrics: Object,
    periodLabel: String,
    prevPeriodLabel: String,
    topCategories: Array,
    chartData: Array,
    availableYears: Array,
    currency: String,
});

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const viewType = ref(props.filters.type || 'monthly');
const selectedYear = ref(props.filters.year || new Date().getFullYear());
const selectedMonth = ref(props.filters.month || new Date().getMonth() + 1);

const months = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

const formatMoney = (amountInCents) => {
    return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: props.currency || 'USD',
    }).format((amountInCents || 0) / 100);
};

const getIconSvg = (iconName) => {
    switch (iconName) {
        case 'shopping-bag':
            return '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>';
        case 'home':
            return '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/>';
        case 'car':
            return '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><circle cx="7" cy="17" r="2"/><path d="M9 17h6"/><circle cx="17" cy="17" r="2"/>';
        case 'utensils':
            return '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>';
        case 'briefcase':
            return '<rect width="20" height="14" x="2" y="7" rx="2" ry="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>';
        case 'heart-pulse':
            return '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>';
        case 'graduation-cap':
            return '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>';
        case 'coffee':
            return '<path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><line x1="6" y1="2" x2="6" y2="4"/><line x1="10" y1="2" x2="10" y2="4"/><line x1="14" y1="2" x2="14" y2="4"/>';
        case 'shopping-cart':
            return '<circle cx="8" cy="21" r="1"/><circle cx="19" cy="21" r="1"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>';
        case 'film':
            return '<rect width="20" height="20" x="2" y="2" rx="2.18" ry="2.18"/><path d="M7 2v20"/><path d="M17 2v20"/><path d="M2 12h20"/><path d="M2 7h5"/><path d="M2 17h5"/><path d="M17 17h5"/><path d="M17 7h5"/>';
        case 'zap':
            return '<polygon points="13 2 3 14 12 14 11 22 21 10 12 10 13 2"/>';
        case 'trending-up':
            return '<polyline points="22 7 13.5 15.5 8.5 10.5 2 17"/><polyline points="16 7 22 7 22 13"/>';
        case 'monitor':
            return '<rect width="20" height="14" x="2" y="3" rx="2"/><line x1="8" x2="16" y1="21" y2="21"/><line x1="12" x2="12" y1="17" y2="21"/>';
        default:
            return '<line x1="12" y1="1" x2="12" y2="23"/><path d="M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/>';
    }
};

const handleFilterChange = () => {
    router.get(route('reports.index'), {
        type: viewType.value,
        year: selectedYear.value,
        month: viewType.value === 'monthly' ? selectedMonth.value : null,
    }, { preserveState: true });
};

watch(viewType, () => {
    // When changing tabs, automatically update filters
    handleFilterChange();
});

// Chart setup
const chartSeries = computed(() => {
    if (viewType.value === 'monthly') {
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
    
    if (viewType.value === 'monthly') {
        return {
            chart: {
                type: 'area',
                fontFamily: 'Inter, sans-serif',
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
                fontFamily: 'Inter, sans-serif',
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

const printReport = () => {
    window.print();
};

const downloadReportData = () => {
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify({
        period: props.periodLabel,
        metrics: props.metrics,
        topCategories: props.topCategories,
        chartData: props.chartData
    }, null, 2));
    const downloadAnchor = document.createElement('a');
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", `waiswallet_report_${props.periodLabel.toLowerCase().replace(' ', '_')}.json`);
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
};
</script>

<template>
    <Head :title="`Financial Report - ${periodLabel}`" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6 print:p-0 print:space-y-4">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Financial Reports</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Review metrics and category distributions for a selected period.</p>
                </div>
                <div class="flex items-center gap-2">
                    <button 
                        @click="printReport"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl border border-input bg-card px-4 text-sm font-semibold text-foreground hover:bg-muted transition-colors shadow-sm"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
                            <path d="M6 9V3a1 1 0 0 1 1-1h10a1 1 0 0 1 1 1v6"/>
                            <rect x="6" y="14" width="12" height="8" rx="1"/>
                        </svg>
                        Print
                    </button>
                    <button 
                        @click="downloadReportData"
                        class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-foreground hover:bg-primary/90 transition-all shadow-md shadow-primary/25"
                    >
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" x2="12" y1="15" y2="3"/>
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            <!-- Print Header -->
            <div class="hidden print:block border-b border-border pb-4 mb-6">
                <div class="flex justify-between items-center">
                    <div>
                        <h1 class="text-3xl font-extrabold tracking-tight text-foreground">WaisWallet Report</h1>
                        <p class="text-sm text-muted-foreground mt-1">Generated financial summary statement</p>
                    </div>
                    <div class="text-right">
                        <span class="text-xl font-bold text-primary">{{ periodLabel }}</span>
                    </div>
                </div>
            </div>

            <!-- Navigation & Filters -->
            <div class="glass-card p-4 print:hidden animate-slide-up flex flex-col md:flex-row gap-4 items-center justify-between">
                <!-- Toggle Mode tabs -->
                <div class="flex bg-muted/60 p-1 rounded-xl w-full md:w-auto">
                    <button 
                        @click="viewType = 'monthly'"
                        :class="[
                            'flex-1 md:flex-none px-6 py-2 text-sm font-medium rounded-lg transition-all',
                            viewType === 'monthly' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        Monthly
                    </button>
                    <button 
                        @click="viewType = 'yearly'"
                        :class="[
                            'flex-1 md:flex-none px-6 py-2 text-sm font-medium rounded-lg transition-all',
                            viewType === 'yearly' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                        ]"
                    >
                        Yearly
                    </button>
                </div>

                <!-- Select Filters -->
                <div class="flex flex-wrap items-center gap-3 w-full md:w-auto justify-end">
                    <select 
                        v-if="viewType === 'monthly'"
                        v-model="selectedMonth"
                        @change="handleFilterChange"
                        class="h-10 rounded-lg border-0 bg-input px-3 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all min-w-[130px]"
                    >
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>

                    <select 
                        v-model="selectedYear"
                        @change="handleFilterChange"
                        class="h-10 rounded-lg border-0 bg-input px-3 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all min-w-[110px]"
                    >
                        <option v-for="y in availableYears" :key="y" :value="y">{{ y }}</option>
                    </select>
                </div>
            </div>

            <!-- Print Context Info -->
            <div class="hidden print:flex items-center justify-between text-sm text-muted-foreground py-2 border-b border-border">
                <div>Report Type: <span class="font-medium text-foreground capitalize">{{ viewType }}</span></div>
                <div>Reporting Period: <span class="font-medium text-foreground">{{ periodLabel }}</span></div>
            </div>

            <!-- Metrics Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-slide-up" style="animation-delay: 0.05s">
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

            <!-- Charts and Categories breakdown row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.1s">
                <!-- Trend Chart -->
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

                <!-- Top Categories -->
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
            </div>

            <!-- Transaction Count Summary and Period Overview -->
            <div class="glass-card p-6 animate-slide-up print:break-inside-avoid" style="animation-delay: 0.15s">
                <h2 class="text-lg font-semibold text-foreground">Transaction & Volume Analytics</h2>
                <p class="text-xs text-muted-foreground mt-0.5">Overview of transaction volumes and average sizing.</p>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mt-6">
                    <div class="bg-muted/30 p-4 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wide">Total Transactions</p>
                            <p class="text-2xl font-bold text-foreground mt-1">{{ metrics.transactionCount }}</p>
                        </div>
                        <div class="h-10 w-10 rounded-lg bg-primary/10 text-primary flex items-center justify-center">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"/><line x1="16" x2="16" y1="2" y2="6"/><line x1="8" x2="8" y1="2" y2="6"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-muted/30 p-4 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wide">Average Transaction Size</p>
                            <p class="text-2xl font-bold text-foreground mt-1 font-financial">
                                {{ formatMoney(metrics.transactionCount > 0 ? Math.round((metrics.income + metrics.expense) / metrics.transactionCount) : 0) }}
                            </p>
                        </div>
                        <div class="h-10 w-10 rounded-lg bg-chart-1/10 text-chart-1 flex items-center justify-center">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <circle cx="12" cy="12" r="10"/><path d="M12 8V12l3 3"/>
                            </svg>
                        </div>
                    </div>

                    <div class="bg-muted/30 p-4 rounded-xl flex items-center justify-between">
                        <div>
                            <p class="text-xs text-muted-foreground font-medium uppercase tracking-wide">Avg. Daily Outflow</p>
                            <p class="text-2xl font-bold text-foreground mt-1 font-financial">
                                {{ formatMoney(viewType === 'monthly' ? Math.round(metrics.expense / 30) : Math.round(metrics.expense / 365)) }}
                            </p>
                        </div>
                        <div class="h-10 w-10 rounded-lg bg-destructive/10 text-destructive flex items-center justify-center">
                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="m3 16 4-4 4 4 6-6 4 4"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Print Footer -->
            <div class="hidden print:block text-center text-xs text-muted-foreground pt-12 border-t border-border mt-12">
                This financial statement is generated by WaisWallet personal budgeting application. All figures are formatted in system default base.
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style scoped>
@media print {
    /* Hide layout parts during print */
    :deep(nav), :deep(header), :deep(aside), .print\:hidden {
        display: none !important;
    }
    
    /* Make the printed content use full width */
    :deep(main) {
        padding: 0 !important;
        margin: 0 !important;
    }
    
    .glass-card {
        background: transparent !important;
        border: 1px solid #e5e7eb !important;
        box-shadow: none !important;
        border-radius: 0 !important;
    }
}
</style>
