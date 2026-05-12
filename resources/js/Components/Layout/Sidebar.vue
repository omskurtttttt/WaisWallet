<script setup>
import { ref } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle']);

const page = usePage();

const navItems = [
    { name: 'Dashboard', href: '/', routeName: 'dashboard', icon: 'dashboard' },
    { name: 'Transactions', href: '/transactions', routeName: 'transactions', icon: 'transactions' },
    { name: 'Wallet', href: '/wallet', routeName: 'wallet', icon: 'wallet' },
    { name: 'Goals', href: '/goals', routeName: 'goals', icon: 'goals' },
    { name: 'Analytics', href: '/analytics', routeName: 'analytics', icon: 'analytics' },
    { name: 'Reports', href: '/reports', routeName: 'reports', icon: 'reports' },
];

function isActive(item) {
    const url = page.url;
    if (item.href === '/') return url === '/';
    return url.startsWith(item.href);
}
</script>

<template>
    <aside
        :class="[
            'fixed left-0 top-0 z-40 h-screen border-r border-sidebar-border bg-sidebar transition-all duration-300 ease-in-out flex flex-col',
            collapsed ? 'w-[72px]' : 'w-[260px]',
        ]"
    >
        <!-- Logo -->
        <div class="flex h-16 items-center gap-3 px-5 border-b border-sidebar-border shrink-0">
            <div class="flex h-9 w-9 items-center justify-center rounded-xl bg-primary shrink-0">
                <svg class="h-5 w-5 text-primary-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                </svg>
            </div>
            <transition
                enter-active-class="transition-opacity duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-opacity duration-150"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <span v-if="!collapsed" class="text-lg font-bold tracking-tight text-sidebar-foreground whitespace-nowrap">
                    Wais<span class="text-primary">Wallet</span>
                </span>
            </transition>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto px-3 py-4 space-y-1">
            <Link
                v-for="item in navItems"
                :key="item.name"
                :href="item.href"
                :class="[
                    'flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all duration-200 group',
                    isActive(item)
                        ? 'bg-primary text-primary-foreground shadow-md'
                        : 'text-sidebar-foreground hover:bg-sidebar-accent',
                    collapsed ? 'justify-center' : '',
                ]"
                :title="collapsed ? item.name : ''"
            >
                <!-- Dashboard Icon -->
                <svg v-if="item.icon === 'dashboard'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="3" y="3" width="7" height="7" rx="1" />
                    <rect x="14" y="3" width="7" height="7" rx="1" />
                    <rect x="3" y="14" width="7" height="7" rx="1" />
                    <rect x="14" y="14" width="7" height="7" rx="1" />
                </svg>

                <!-- Transactions Icon -->
                <svg v-else-if="item.icon === 'transactions'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                </svg>

                <!-- Wallet Icon -->
                <svg v-else-if="item.icon === 'wallet'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                </svg>

                <!-- Goals Icon -->
                <svg v-else-if="item.icon === 'goals'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="10" />
                    <circle cx="12" cy="12" r="6" />
                    <circle cx="12" cy="12" r="2" />
                </svg>

                <!-- Analytics Icon -->
                <svg v-else-if="item.icon === 'analytics'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M3 3v18h18" />
                    <path d="m19 9-5 5-4-4-3 3" />
                </svg>

                <!-- Reports Icon -->
                <svg v-else-if="item.icon === 'reports'" class="h-5 w-5 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                    <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                    <path d="M10 18v-4" />
                    <path d="M14 18v-6" />
                </svg>

                <transition
                    enter-active-class="transition-opacity duration-200"
                    enter-from-class="opacity-0"
                    enter-to-class="opacity-100"
                    leave-active-class="transition-opacity duration-150"
                    leave-from-class="opacity-100"
                    leave-to-class="opacity-0"
                >
                    <span v-if="!collapsed" class="whitespace-nowrap">{{ item.name }}</span>
                </transition>
            </Link>
        </nav>

        <!-- Collapse Toggle -->
        <div class="border-t border-sidebar-border p-3 shrink-0">
            <button
                @click="$emit('toggle')"
                class="flex w-full items-center justify-center rounded-xl p-2.5 text-sidebar-foreground hover:bg-sidebar-accent transition-colors duration-200"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <svg
                    class="h-5 w-5 transition-transform duration-300"
                    :class="{ 'rotate-180': collapsed }"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                >
                    <path d="m11 17-5-5 5-5" />
                    <path d="m18 17-5-5 5-5" />
                </svg>
            </button>
        </div>
    </aside>
</template>
