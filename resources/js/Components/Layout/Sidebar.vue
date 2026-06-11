<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
    mobileOpen: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['toggle', 'closeMobile']);

const page = usePage();

// Whether sidebar is showing expanded content (either not collapsed or mobile open)
const isExpanded = computed(() => !props.collapsed || props.mobileOpen);

const mainNavItems = [
    { name: 'Dashboard', routeName: 'dashboard', icon: 'dashboard' },
    { name: 'Transactions', routeName: 'transactions.index', icon: 'transactions' },
    { name: 'Wallet', routeName: 'wallet.index', icon: 'wallet' },
    { name: 'Goals', routeName: 'goals.index', icon: 'goals' },
    { name: 'Analytics', routeName: 'analytics.index', icon: 'analytics' },
    { name: 'Reports', routeName: 'reports.index', icon: 'reports' },
];

function isActive(item) {
    const baseRouteName = item.routeName.split('.')[0];
    return route().current(item.routeName) || route().current(`${baseRouteName}.*`);
}

function handleNavClick() {
    if (props.mobileOpen) {
        emit('closeMobile');
    }
}

</script>

<template>
    <!-- Mobile Overlay -->
    <transition
        enter-active-class="transition-opacity duration-300"
        enter-from-class="opacity-0"
        enter-to-class="opacity-100"
        leave-active-class="transition-opacity duration-200"
        leave-from-class="opacity-100"
        leave-to-class="opacity-0"
    >
        <div
            v-if="mobileOpen"
            class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
            @click="$emit('closeMobile')"
        />
    </transition>

    <!-- Sidebar -->
    <aside
        :class="[
            'fixed left-0 top-0 z-50 h-screen bg-sidebar border-r border-sidebar-border flex flex-col',
            'transition-all duration-300 ease-in-out',
            // Desktop and Mobile positioning
            'lg:translate-x-0',
            mobileOpen ? 'max-lg:translate-x-0 max-lg:w-72' : 'max-lg:translate-x-[-100%]',
            collapsed ? 'lg:w-20' : 'lg:w-72',
            isExpanded ? 'sidebar-expanded' : 'sidebar-collapsed',
        ]"
    >
        <!-- Logo Section — Clickable to toggle collapse/expand on desktop -->
        <div
            :class="[
                'flex items-center shrink-0 transition-all duration-300',
                !isExpanded ? 'h-[72px] justify-center px-0' : 'h-[72px] px-6 gap-3',
            ]"
        >
            <button
                @click="$emit('toggle')"
                class="hidden lg:flex h-10 w-10 items-center justify-center rounded-[14px] bg-primary shrink-0 shadow-md shadow-primary/20 transition-all duration-200 hover:shadow-lg hover:shadow-primary/30 hover:scale-105 cursor-pointer"
                :title="collapsed ? 'Expand sidebar' : 'Collapse sidebar'"
            >
                <svg class="h-5 w-5 text-primary-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                </svg>
            </button>
            <!-- Mobile: non-clickable logo -->
            <div class="flex h-10 w-10 items-center justify-center rounded-[14px] bg-primary shrink-0 shadow-md shadow-primary/20 lg:hidden">
                <svg class="h-5 w-5 text-primary-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                    <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                    <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                </svg>
            </div>
            
            <span
                :class="[
                    !isExpanded ? 'opacity-0 w-0 overflow-hidden' : 'opacity-100 w-auto ml-3'
                ]"
                class="text-[17px] font-extrabold tracking-tight text-sidebar-foreground whitespace-nowrap transition-all duration-300 ease-in-out"
            >
                WAIS<span class="text-primary">WALLET</span>
            </span>
        </div>

        <!-- Logo Divider (Only shown when collapsed) -->
        <div
            :class="[
                !isExpanded ? 'opacity-100 h-auto py-2' : 'opacity-0 h-0 overflow-hidden py-0'
            ]"
            class="flex justify-center shrink-0 transition-all duration-300 ease-in-out"
        >
            <div class="h-px bg-sidebar-border/70 rounded-full w-6"></div>
        </div>

        <!-- Navigation -->
        <nav class="flex-1 overflow-y-auto overflow-x-hidden py-5 sidebar-scrollable">
            <!-- Main Navigation Section -->
            <div :class="!isExpanded ? 'px-2.5' : 'px-4'">
                <p
                    :class="[
                        !isExpanded ? 'opacity-0 h-0 overflow-hidden pb-0' : 'opacity-100 h-auto pb-3'
                    ]"
                    class="px-3 text-[10px] font-bold uppercase tracking-[0.12em] text-muted-foreground/70 transition-all duration-300 ease-in-out whitespace-nowrap"
                >
                    Navigation
                </p>

                <div class="space-y-1">
                    <Link
                        v-for="item in mainNavItems"
                        :key="item.name"
                        :href="route(item.routeName)"
                        @click="handleNavClick"
                        :class="[
                            'sidebar-nav-item flex items-center rounded-[12px] text-[13px] font-semibold transition-all duration-300 ease-in-out group relative',
                            isActive(item)
                                ? 'bg-sidebar-accent text-sidebar-foreground sidebar-nav-active'
                                : 'text-muted-foreground hover:bg-sidebar-accent/50 hover:text-sidebar-foreground',
                            !isExpanded
                                ? 'justify-center p-2.5'
                                : 'gap-3 px-3 py-2.5',
                        ]"
                        :title="!isExpanded ? '' : item.name"
                    >
                        <!-- Dashboard Icon -->
                        <svg v-if="item.icon === 'dashboard'" :class="['h-[20px] w-[20px] shrink-0 transition-colors duration-300 ease-in-out', isActive(item) ? 'text-primary' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="7" height="7" rx="1.5" />
                            <rect x="14" y="3" width="7" height="7" rx="1.5" />
                            <rect x="3" y="14" width="7" height="7" rx="1.5" />
                            <rect x="14" y="14" width="7" height="7" rx="1.5" />
                        </svg>

                        <!-- Transactions Icon (Philippine Peso ₱) -->
                        <svg v-else-if="item.icon === 'transactions'" :class="['h-[20px] w-[20px] shrink-0 transition-colors duration-300 ease-in-out', isActive(item) ? 'text-primary' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 11H4" />
                            <path d="M20 7H4" />
                            <path d="M7 21V4a1 1 0 0 1 1-1h4a1 1 0 0 1 0 12H7" />
                        </svg>

                        <!-- Wallet Icon -->
                        <svg v-else-if="item.icon === 'wallet'" :class="['h-[20px] w-[20px] shrink-0 transition-colors duration-300 ease-in-out', isActive(item) ? 'text-primary' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                            <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                        </svg>

                        <!-- Goals Icon -->
                        <svg v-else-if="item.icon === 'goals'" :class="['h-[20px] w-[20px] shrink-0 transition-colors duration-300 ease-in-out', isActive(item) ? 'text-primary' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <circle cx="12" cy="12" r="10" />
                            <circle cx="12" cy="12" r="6" />
                            <circle cx="12" cy="12" r="2" />
                        </svg>

                        <!-- Analytics Icon -->
                        <svg v-else-if="item.icon === 'analytics'" :class="['h-[20px] w-[20px] shrink-0 transition-colors duration-300 ease-in-out', isActive(item) ? 'text-primary' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M3 3v18h18" />
                            <path d="m19 9-5 5-4-4-3 3" />
                        </svg>

                        <!-- Reports Icon -->
                        <svg v-else-if="item.icon === 'reports'" :class="['h-[20px] w-[20px] shrink-0 transition-colors duration-300 ease-in-out', isActive(item) ? 'text-primary' : '']" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            <path d="M10 18v-4" />
                            <path d="M14 18v-6" />
                        </svg>

                        <span
                            :class="[
                                !isExpanded ? 'opacity-0 w-0 overflow-hidden ml-0' : 'opacity-100 w-auto ml-3'
                            ]"
                            class="whitespace-nowrap transition-all duration-300 ease-in-out font-medium"
                        >
                            {{ item.name }}
                        </span>

                        <!-- Collapsed tooltip -->
                        <div
                            v-if="!isExpanded"
                            class="absolute left-full ml-3 hidden group-hover:flex items-center z-50"
                        >
                            <div class="rounded-lg bg-foreground px-3 py-1.5 text-xs font-medium text-background shadow-lg whitespace-nowrap">
                                {{ item.name }}
                            </div>
                        </div>
                    </Link>
                </div>
            </div>

        </nav>

    </aside>
</template>

<style scoped>
/* Hide scrollbar for sidebar nav but keep scrollable */
.sidebar-scrollable {
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.sidebar-scrollable::-webkit-scrollbar {
    display: none;
}

/* Active nav item subtle left accent bar */
.sidebar-nav-active::before {
    content: '';
    position: absolute;
    left: -4px;
    top: 50%;
    transform: translateY(-50%);
    width: 3px;
    height: 20px;
    border-radius: 0 4px 4px 0;
    background: var(--primary);
    opacity: 0;
    transition: opacity 0.3s ease;
}

/* Only show the accent bar in expanded mode */
.sidebar-expanded .sidebar-nav-active::before {
    opacity: 1;
}
</style>
