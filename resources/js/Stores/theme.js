import { defineStore } from 'pinia';
import { ref, watch } from 'vue';
import { router, usePage } from '@inertiajs/vue3';

export const useThemeStore = defineStore('theme', () => {
    const isDark = ref(false);

    // Initialize from localStorage, DB preference, or system preference
    function init() {
        let dbTheme = null;
        try {
            const page = usePage();
            dbTheme = page.props.auth?.user?.theme_preference;
        } catch (e) {
            // usePage might fail if called outside Inertia context
        }

        if (dbTheme) {
            isDark.value = dbTheme === 'dark';
        } else {
            const stored = localStorage.getItem('waiswallet-theme');
            if (stored) {
                isDark.value = stored === 'dark';
            } else {
                isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
            }
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

    function syncThemeToBackend(themeStr) {
        try {
            const page = usePage();
            if (page && page.props.auth?.user) {
                // Get the encrypted XSRF-TOKEN cookie set by Laravel
                const csrfToken = document.cookie
                    .split('; ')
                    .find(row => row.startsWith('XSRF-TOKEN='))
                    ?.split('=')[1];

                if (csrfToken) {
                    fetch('/profile/theme', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-XSRF-TOKEN': decodeURIComponent(csrfToken),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        body: JSON.stringify({ theme: themeStr })
                    });
                } else {
                    // Fallback to router.post if cookie is not readable (e.g. local environment setups)
                    router.post('/profile/theme', { theme: themeStr }, { 
                        preserveScroll: true, 
                        preserveState: true,
                        showProgress: false 
                    });
                }
            }
        } catch (e) {
            // Fail silently
        }
    }

    function toggle() {
        isDark.value = !isDark.value;
        const themeStr = isDark.value ? 'dark' : 'light';
        localStorage.setItem('waiswallet-theme', themeStr);
        applyTheme();
        syncThemeToBackend(themeStr);
    }

    function setTheme(dark) {
        isDark.value = dark;
        const themeStr = dark ? 'dark' : 'light';
        localStorage.setItem('waiswallet-theme', themeStr);
        applyTheme();
        syncThemeToBackend(themeStr);
    }

    // Watch for changes
    watch(isDark, () => {
        applyTheme();
    });

    return { isDark, init, toggle, setTheme };
});
