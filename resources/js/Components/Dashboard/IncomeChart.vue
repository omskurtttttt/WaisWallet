<script setup>
import { computed, ref, watch } from 'vue';
import VueApexCharts from 'vue3-apexcharts';
import { useThemeStore } from '@/Stores/theme';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    },
    currency: {
        type: String,
        default: 'USD'
    }
});

const themeStore = useThemeStore();
const isDark = computed(() => themeStore.isDark);

const chartOptions = computed(() => ({
    chart: {
        type: 'bar',
        fontFamily: 'Inter, sans-serif',
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
    colors: ['#7033ff'],
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
                colors: isDark.value ? '#9CA3AF' : '#6B7280',
                fontSize: '12px'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: isDark.value ? '#9CA3AF' : '#6B7280',
                fontSize: '12px',
                fontFamily: 'IBM Plex Mono, monospace'
            },
            formatter: (value) => {
                return new Intl.NumberFormat('en-US', {
                    style: 'currency',
                    currency: props.currency,
                    maximumFractionDigits: 0
                }).format(value);
            }
        }
    },
    grid: {
        borderColor: isDark.value ? '#374151' : '#E5E7EB',
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
            gradientToColors: ['#9c77ff'],
            inverseColors: true,
            opacityFrom: 1,
            opacityTo: 0.8,
            stops: [0, 100]
        }
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

const series = computed(() => [{
    name: 'Income',
    data: props.data.map(item => item.amount)
}]);
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">Total Income</h2>
            <select class="h-9 rounded-lg border border-border bg-input px-3 text-sm text-foreground focus:outline-none focus:ring-2 focus:ring-primary/30">
                <option>Last 6 months</option>
                <option disabled>This year (Coming Soon)</option>
            </select>
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
