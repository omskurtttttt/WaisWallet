<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { useCurrency } from '@/Composables/useCurrency';
import ReportFilters from './Partials/ReportFilters.vue';
import MetricCards from './Partials/MetricCards.vue';
import ReportChart from './Partials/ReportChart.vue';
import TopCategories from './Partials/TopCategories.vue';
import Dropdown from '@/Components/Dropdown.vue';

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

const viewType = ref(props.filters.type || 'monthly');
const selectedYear = ref(props.filters.year || new Date().getFullYear());
const selectedMonth = ref(props.filters.month || new Date().getMonth() + 1);

const { formatMoney } = useCurrency();

const handleFilterChange = () => {
    router.get(route('reports.index'), {
        type: viewType.value,
        year: selectedYear.value,
        month: viewType.value === 'monthly' ? selectedMonth.value : null,
    }, { preserveState: true });
};

watch(viewType, () => {
    handleFilterChange();
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

const downloadCsvReport = () => {
    const url = route('reports.index', {
        type: viewType.value,
        year: selectedYear.value,
        month: viewType.value === 'monthly' ? selectedMonth.value : null,
        export: 'csv'
    });
    window.location.href = url;
};
</script>

<template>
    <Head :title="`Financial Report - ${periodLabel}`" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6 print:p-0 print:space-y-4">
            <!-- Header section -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 print:hidden animate-fade-in relative z-30">
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
                    
                    <Dropdown align="right" width="48" content-classes="py-1 bg-card border border-border rounded-xl shadow-xl overflow-hidden">
                        <template #trigger>
                            <button 
                                class="inline-flex h-10 items-center justify-center gap-2 rounded-xl bg-primary px-4 text-sm font-semibold text-primary-foreground hover:bg-primary/90 transition-all shadow-md shadow-primary/25 cursor-pointer"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" x2="12" y1="15" y2="3"/>
                                </svg>
                                <span>Export</span>
                                <svg class="h-3.5 w-3.5 opacity-60 ml-0.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                    <polyline points="6 9 12 15 18 9"/>
                                </svg>
                            </button>
                        </template>
                        <template #content>
                            <button 
                                @click="downloadReportData"
                                class="block w-full px-4 py-2.5 text-start text-sm leading-5 text-foreground hover:bg-muted focus:bg-muted focus:outline-none transition duration-150 ease-in-out font-medium cursor-pointer"
                            >
                                Export JSON (Summary)
                            </button>
                            <button 
                                @click="downloadCsvReport"
                                class="block w-full px-4 py-2.5 text-start text-sm leading-5 text-foreground hover:bg-muted focus:bg-muted focus:outline-none transition duration-150 ease-in-out font-medium cursor-pointer"
                            >
                                Export CSV (Transactions)
                            </button>
                        </template>
                    </Dropdown>
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
            <ReportFilters 
                v-model:viewType="viewType"
                v-model:selectedMonth="selectedMonth"
                v-model:selectedYear="selectedYear"
                :availableYears="availableYears"
                @change="handleFilterChange"
            />

            <!-- Print Context Info -->
            <div class="hidden print:flex items-center justify-between text-sm text-muted-foreground py-2 border-b border-border">
                <div>Report Type: <span class="font-medium text-foreground capitalize">{{ viewType }}</span></div>
                <div>Reporting Period: <span class="font-medium text-foreground">{{ periodLabel }}</span></div>
            </div>

            <!-- Metrics Grid -->
            <MetricCards 
                :metrics="metrics"
                :prevPeriodLabel="prevPeriodLabel"
            />

            <!-- Charts and Categories breakdown row -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 animate-slide-up" style="animation-delay: 0.1s">
                <ReportChart 
                    :view-type="viewType"
                    :chart-data="chartData"
                    :period-label="periodLabel"
                />

                <TopCategories 
                    :top-categories="topCategories"
                    :period-label="periodLabel"
                />
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
