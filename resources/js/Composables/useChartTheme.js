import { computed } from 'vue';
import { useThemeStore } from '@/Stores/theme';

export function useChartTheme() {
    const themeStore = useThemeStore();

    const getCssVar = (name) => {
        if (typeof window === 'undefined') return '';
        return getComputedStyle(document.documentElement).getPropertyValue(name).trim();
    };

    const chartColors = computed(() => {
        // Track themeStore.isDark dependency so this computed property re-runs
        const isDark = themeStore.isDark;
        
        return {
            chart1: getCssVar('--chart-1') || (isDark ? '#4ade80' : '#4ac885'),
            chart2: getCssVar('--chart-2') || (isDark ? '#8c5cff' : '#7033ff'),
            chart3: getCssVar('--chart-3') || (isDark ? '#fca5a5' : '#fd822b'),
            chart4: getCssVar('--chart-4') || (isDark ? '#5993f4' : '#3276e4'),
            chart5: getCssVar('--chart-5') || (isDark ? '#a0a0a0' : '#747474'),
            primary: getCssVar('--primary') || (isDark ? '#8c5cff' : '#7033ff'),
            destructive: getCssVar('--destructive') || (isDark ? '#f87171' : '#e54b4f'),
            background: getCssVar('--background') || (isDark ? '#1a1b1e' : '#fdfdfd'),
            card: getCssVar('--card') || (isDark ? '#222327' : '#fdfdfd'),
            border: getCssVar('--border') || (isDark ? '#33353a' : '#e7e7ee'),
            textMuted: getCssVar('--muted-foreground') || (isDark ? '#a0a0a0' : '#525252'),
            text: getCssVar('--foreground') || (isDark ? '#f0f0f0' : '#000000'),
        };
    });

    return {
        chartColors,
    };
}
