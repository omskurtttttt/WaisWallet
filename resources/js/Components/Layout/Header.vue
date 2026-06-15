<script setup>
import { useThemeStore } from '@/Stores/theme';
import { usePage, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';

const props = defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const emit = defineEmits(['openMobile']);

const themeStore = useThemeStore();
const page = usePage();

const searchQuery = ref('');

// Sync search query input with URL query param across page changes
watch(() => page.url, (url) => {
    try {
        const urlObj = new URL(url, window.location.origin);
        searchQuery.value = urlObj.searchParams.get('search') || '';
    } catch (e) {
        searchQuery.value = '';
    }
}, { immediate: true });

function handleSearch() {
    router.get(route('transactions.index'), { search: searchQuery.value });
}

function handleMobileSearchClick() {
    router.get(route('transactions.index'));
}

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

const notifications = computed(() => page.props.notifications);

function markAsRead(id) {
    router.patch(route('notifications.read', id), {}, {
        preserveScroll: true,
    });
}

function markAllAsRead() {
    router.post(route('notifications.read-all'), {}, {
        preserveScroll: true,
    });
}
</script>

<template>
    <header
        :class="[
            'absolute top-0 right-0 z-30 flex h-[72px] items-center justify-between px-4 md:px-6 transition-all duration-300',
            'left-0',
            collapsed ? 'lg:left-20' : 'lg:left-72',
        ]"
    >
        <!-- Left: Mobile menu + Logo + Search -->
        <div class="flex items-center gap-3">
            <!-- Mobile Hamburger -->
            <button
                @click="$emit('openMobile')"
                class="flex h-10 w-10 items-center justify-center rounded-[12px] text-muted-foreground hover:bg-sidebar-accent hover:text-foreground transition-all duration-200 lg:hidden"
                title="Open menu"
            >
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="4" y1="6" x2="20" y2="6" />
                    <line x1="4" y1="12" x2="16" y2="12" />
                    <line x1="4" y1="18" x2="12" y2="18" />
                </svg>
            </button>

            <!-- Mobile Logo (shown only on mobile when sidebar is hidden) -->
            <div class="flex items-center gap-2.5 lg:hidden">
                <div class="flex h-9 w-9 items-center justify-center rounded-[12px] bg-primary shrink-0 shadow-sm shadow-primary/15">
                    <svg class="h-4.5 w-4.5 text-primary-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                    </svg>
                </div>
                <span class="text-[15px] font-extrabold tracking-tight text-foreground">
                    WAIS<span class="text-primary">WALLET</span>
                </span>
            </div>

            <!-- Search -->
            <div class="relative hidden sm:block">
                <svg class="absolute left-3.5 top-1/2 -translate-y-1/2 h-4 w-4 text-muted-foreground pointer-events-none" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
                <input
                    v-model="searchQuery"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="Search transactions..."
                    class="h-10 w-52 md:w-64 rounded-[12px] border border-border bg-card pl-10 pr-4 text-sm text-foreground placeholder:text-muted-foreground/60 focus:outline-none focus:ring-2 focus:ring-primary/25 focus:border-primary/40 transition-all duration-200"
                />
            </div>

            <!-- Mobile Search (icon only) -->
            <button @click="handleMobileSearchClick" class="flex h-10 w-10 items-center justify-center rounded-[12px] text-muted-foreground hover:bg-card hover:text-foreground transition-all duration-200 sm:hidden" title="Search">
                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="11" cy="11" r="8" />
                    <path d="m21 21-4.3-4.3" />
                </svg>
            </button>
        </div>

        <!-- Right: Actions -->
        <div class="flex items-center gap-1 md:gap-1.5">
            <!-- Dark Mode Toggle -->
            <button
                @click="themeStore.toggle()"
                class="flex h-10 w-10 items-center justify-center rounded-[12px] text-muted-foreground hover:bg-card hover:text-foreground transition-all duration-200"
                :title="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <!-- Sun Icon (shown in dark mode) -->
                <svg v-if="themeStore.isDark" class="h-[18px] w-[18px] transition-transform duration-300 hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
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
                <svg v-else class="h-[18px] w-[18px] transition-transform duration-300 hover:-rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                </svg>
            </button>

            <!-- Notifications -->
            <Dropdown align="right" width="80" content-classes="py-2 bg-card border border-border rounded-xl shadow-xl overflow-hidden">
                <template #trigger>
                    <button class="relative flex h-10 w-10 items-center justify-center rounded-[12px] text-muted-foreground hover:bg-card hover:text-foreground transition-all duration-200" title="Notifications">
                        <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M6 8a6 6 0 0 1 12 0c0 7 3 9 3 9H3s3-2 3-9" />
                            <path d="M10.3 21a1.94 1.94 0 0 0 3.4 0" />
                        </svg>
                        <!-- Unread Badge count -->
                        <span 
                            v-if="notifications?.unread_count > 0"
                            class="absolute top-1.5 right-1.5 flex h-4.5 min-w-4.5 items-center justify-center rounded-full bg-destructive px-1 text-[9px] font-bold text-white ring-2 ring-background"
                        >
                            {{ notifications.unread_count }}
                        </span>
                    </button>
                </template>
                <template #content>
                    <!-- Header -->
                    <div class="px-4 py-2 border-b border-border flex justify-between items-center bg-muted/20">
                        <span class="text-xs font-bold text-foreground">Notifications</span>
                        <button 
                            v-if="notifications?.unread_count > 0"
                            @click.stop="markAllAsRead"
                            class="text-[10px] font-semibold text-primary hover:text-primary/80 transition-colors"
                        >
                            Mark all read
                        </button>
                    </div>

                    <!-- List -->
                    <div class="max-h-[300px] overflow-y-auto divide-y divide-border/60">
                        <div 
                            v-if="notifications?.latest.length > 0"
                            v-for="item in notifications.latest" 
                            :key="item.id"
                            :class="[
                                'px-4 py-3 flex gap-3 transition-colors duration-150',
                                !item.read_at ? 'bg-primary/5 hover:bg-primary/10' : 'hover:bg-muted/50'
                            ]"
                        >
                            <!-- Notification Type Icon -->
                            <div class="mt-0.5 shrink-0 flex items-center justify-center h-8 w-8 rounded-full" 
                                 :class="[
                                     item.type === 'budget_alert' ? 'bg-destructive/10 text-destructive' : 'bg-chart-1/10 text-chart-1'
                                 ]">
                                <svg v-if="item.type === 'budget_alert'" class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <svg v-else class="h-4.5 w-4.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138z"/>
                                </svg>
                            </div>

                            <!-- Text Content -->
                            <div class="flex-1 min-w-0 text-left">
                                <p class="text-xs font-bold text-foreground truncate">{{ item.title }}</p>
                                <p class="text-[11px] text-muted-foreground mt-0.5 leading-relaxed">{{ item.message }}</p>
                                <p class="text-[9px] text-muted-foreground/60 mt-1 font-semibold uppercase tracking-wider">{{ item.created_ago }}</p>
                            </div>

                            <!-- Mark Read Button -->
                            <button 
                                v-if="!item.read_at"
                                @click.stop="markAsRead(item.id)"
                                class="shrink-0 h-5 w-5 rounded-full hover:bg-primary/20 flex items-center justify-center text-muted-foreground hover:text-primary transition-all duration-150"
                                title="Mark read"
                            >
                                <svg class="h-3 w-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                                    <polyline points="20 6 9 17 4 12"/>
                                </svg>
                            </button>
                        </div>
                        <div v-else class="px-4 py-8 text-center text-xs text-muted-foreground">
                            No notifications yet
                        </div>
                    </div>
                </template>
            </Dropdown>

            <!-- Settings -->
            <Link
                :href="route('profile.edit')"
                class="hidden md:flex h-10 w-10 items-center justify-center rounded-[12px] text-muted-foreground hover:bg-card hover:text-foreground transition-all duration-200"
                title="Settings"
            >
                <svg class="h-[18px] w-[18px]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z" />
                    <circle cx="12" cy="12" r="3" />
                </svg>
            </Link>

            <!-- Divider -->
            <div class="mx-1 h-8 w-px bg-border/50 hidden md:block"></div>

            <!-- User Avatar & Dropdown -->
            <Dropdown align="right" width="48" content-classes="py-1 bg-card border border-border rounded-xl overflow-hidden">
                <template #trigger>
                    <button class="flex items-center gap-3 rounded-[12px] px-2 md:px-3 py-1.5 hover:bg-card transition-all duration-200 cursor-pointer">
                        <div class="flex h-9 w-9 items-center justify-center rounded-full bg-primary/15 text-primary font-bold text-sm shrink-0 ring-2 ring-primary/20">
                            <img
                                v-if="user?.avatar"
                                :src="user.avatar"
                                :alt="user?.name"
                                class="h-full w-full rounded-full object-cover"
                            />
                            <span v-else>{{ initials }}</span>
                        </div>
                        <div class="hidden lg:block text-left" v-if="user">
                            <p class="text-[13px] font-bold text-foreground leading-tight">{{ user.name }}</p>
                            <p class="text-[11px] text-muted-foreground leading-tight">{{ user.email }}</p>
                        </div>
                    </button>
                </template>
                <template #content>
                    <DropdownLink :href="route('profile.edit')" class="text-foreground hover:bg-muted font-medium rounded-t-xl">
                        Profile Settings
                    </DropdownLink>
                    <DropdownLink :href="route('logout')" method="post" as="button" class="w-full text-left text-destructive hover:bg-muted font-medium rounded-b-xl">
                        Log Out
                    </DropdownLink>
                </template>
            </Dropdown>
        </div>
    </header>
</template>
