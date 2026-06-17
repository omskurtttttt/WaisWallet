<script setup>
import { computed, ref, watch, defineAsyncComponent } from 'vue';
const VueApexCharts = defineAsyncComponent(() => import('vue3-apexcharts'));
import { useThemeStore } from '@/Stores/theme';
import CustomSelect from '@/Components/CustomSelect.vue';
import { useChartTheme } from '@/Composables/useChartTheme';
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

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);
const { chartColors } = useChartTheme();
const { formatMoney } = useCurrency();

const period = ref('Last 6 months');
const periodOptions = [
    { value: 'Last 6 months', label: 'Last 6 months' },
    { value: 'This year', label: 'This year (Coming Soon)', disabled: true }
];

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        fontFamily: 'Plus Jakarta Sans, sans-serif',
        toolbar: { show: false },
        animations: {
            enabled: true,
            easing: 'easeinout',
            speed: 800,
            animateGradually: { enabled: true, delay: 150 },
            dynamicAnimation: { enabled: true, speed: 350 }
        },
        background: 'transparent'
    },
    colors: [chartColors.value.chart2],
    plotOptions: {
        bar: {
            borderRadius: 6,
            columnWidth: '40%',
            dataLabels: { position: 'top' },
        }
    },
    dataLabels: {
        enabled: false,
    },
    stroke: {
        show: true,
        width: 2,
        colors: ['transparent']
    },
    xaxis: {
        categories: props.data.map(item => item.month),
        axisBorder: { show: false },
        axisTicks: { show: false },
        labels: {
            style: {
                colors: chartColors.value.textMuted,
                fontSize: '11px',
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: chartColors.value.textMuted,
                fontSize: '11px',
                fontFamily: 'IBM Plex Mono, monospace'
            },
            formatter: (value) => formatMoney(value * 100, { maximumFractionDigits: 0 })
        }
    },
    grid: {
        borderColor: chartColors.value.border,
        strokeDashArray: 4,
        yaxis: { lines: { show: true } },
        xaxis: { lines: { show: false } },
        padding: { top: 0, right: 0, bottom: 0, left: 10 }
    },
    fill: {
        opacity: 1,
        type: 'gradient',
        gradient: {
            shade: isDark.value ? 'dark' : 'light',
            type: 'vertical',
            shadeIntensity: 0.5,
            gradientToColors: [isDark.value ? chartColors.value.chart4 : chartColors.value.primary],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 0.8,
            stops: [0, 100]
        }
    },
    tooltip: {
        theme: isDark.value ? 'dark' : 'light',
        y: {
            formatter: (val) => formatMoney(val * 100)
        }
    }
}));

const series = computed(() => [{
    name: 'Income',
    data: props.data.map(item => item.amount)
}]);
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between gap-4 mb-4">
            <h2 class="text-lg font-semibold text-foreground whitespace-nowrap">Total Income</h2>
            <CustomSelect
                v-model="period"
                :options="periodOptions"
                class="w-36"
                trigger-class="h-9 rounded-lg"
            />
        </div>
        <div class="h-[300px] w-full">
            <VueApexCharts
                type="bar"
                height="100%"
                :options="chartOptions"
                :series="series"
            />
        </div>
    </div>
</template>
