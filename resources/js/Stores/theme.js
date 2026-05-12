import { defineStore } from 'pinia';
import { ref, watch } from 'vue';

export const useThemeStore = defineStore('theme', () => {
    const isDark = ref(false);

    // Initialize from localStorage or system preference
    function init() {
        const stored = localStorage.getItem('waiswallet-theme');
        if (stored) {
            isDark.value = stored === 'dark';
        } else {
            isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
        }
        applyTheme();
    }

    function applyTheme() {
        const html = document.documentElement;
        if (isDark.value) {
            html.classList.add('dark');
        } else {
            html.classList.remove('dark');
        }
    }

    function toggle() {
        isDark.value = !isDark.value;
        localStorage.setItem('waiswallet-theme', isDark.value ? 'dark' : 'light');
        applyTheme();
    }

    function setTheme(dark) {
        isDark.value = dark;
        localStorage.setItem('waiswallet-theme', dark ? 'dark' : 'light');
        applyTheme();
    }

    // Watch for changes
    watch(isDark, () => {
        applyTheme();
    });

    return { isDark, init, toggle, setTheme };
});
