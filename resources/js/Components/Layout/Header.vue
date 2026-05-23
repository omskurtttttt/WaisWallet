<script setup>
import { useThemeStore } from '@/Stores/theme';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['openMobile']);

const themeStore = useThemeStore();
const page = usePage();

const user = computed(() => page.props.auth?.user);
const initials = computed(() => {
    if (!user.value || !user.value.name) return 'U';
    return user.value.name
        .split(' ')
        .map(n => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();
});
</script>

<template>
    <header
        :class="[
            'fixed top-0 right-0 z-30 flex h-16 items-center justify-between border-b border-border bg-background/80 backdrop-blur-md px-4 md:px-6 transition-all duration-300',
            'left-0 lg:left-[260px]',
            collapsed ? 'lg:left-[72px]' : 'lg:left-[260px]',
        ]"
    >
        <!-- Left: Mobile menu + Search -->
        <div class="flex items-center gap-3">
            <!-- Mobile Hamburger -->
            <button
                @click="$emit('openMobile')"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground transition-all duration-200 lg:hidden"
                title="Open menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6" />
                    <line x1="4" y1="12" x2="20" y2="12" />
                    <line x1="4" y1="18" x2="20" y2="18" />
                </svg>
            </button>

            <!-- Search -->
            <div class="relative hidden sm:block">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input
                    type="text"
                    placeholder="Search transactions..."
                    class="h-10 w-56 md:w-72 rounded-xl border-0 bg-input pl-10 pr-4 text-sm text-foreground placeholder:text-muted-foreground focus:outline-none focus:ring-2 focus:ring-primary/30 transition-all duration-200"
                />
            </div>

            <!-- Mobile Search (icon only) -->
            <button class="flex h-10 w-10 items-center justify-center rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground transition-all duration-200 sm:hidden" title="Search">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </button>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-1 md:gap-2">
            <!-- Dark Mode Toggle -->
            <button
                @click="themeStore.toggle()"
                class="flex h-10 w-10 items-center justify-center rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground transition-all duration-200"
                :title="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <!-- Sun Icon (shown in dark mode) -->
                <svg v-if="themeStore.isDark" class="h-5 w-5 transition-transform duration-300 hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2" />
                    <path d="M12 20v2" />
                    <path d="m4.93 4.93 1.41 1.41" />
                    <path d="m17.66 17.66 1.41 1.41" />
                    <path d="M2 12h2" />
                    <path d="M20 12h2" />
                    <path d="m6.34 17.66-1.41 1.41" />
                    <path d="m19.07 4.93-1.41 1.41" />
                </svg>
                <!-- Moon Icon (shown in light mode) -->
                <svg v-else class="h-5 w-5 transition-transform duration-300 hover:-rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                </svg>
            </button>

            <!-- Notifications -->
            <button class="relative flex h-10 w-10 items-center justify-center rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground transition-all duration-200" title="Notifications">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                    <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                </svg>
                <span class="absolute right-2 top-2 flex h-2 w-2">
                    <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-destructive opacity-75"></span>
                    <span class="relative inline-flex h-2 w-2 rounded-full bg-destructive"></span>
                </span>
            </button>

            <!-- Settings -->
            <button class="flex h-10 w-10 items-center justify-center rounded-xl text-muted-foreground hover:bg-muted hover:text-foreground transition-all duration-200 hidden md:flex" title="Settings">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </button>

            <!-- Divider -->
            <div class="mx-1 h-8 w-px bg-border hidden md:block"></div>

            <!-- User Avatar -->
            <button class="flex items-center gap-3 rounded-xl px-2 md:px-3 py-2 hover:bg-muted transition-all duration-200">
                <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/10 text-primary font-semibold text-sm shrink-0">
                    {{ initials }}
                </div>
                <div class="hidden lg:block text-left" v-if="user">
                    <p class="text-sm font-semibold text-foreground leading-tight">{{ user.name }}</p>
                    <p class="text-xs text-muted-foreground leading-tight">{{ user.email }}</p>
                </div>
            </button>
        </div>
    </header>
</template>
