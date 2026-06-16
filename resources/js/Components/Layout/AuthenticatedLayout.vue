<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import Toast from '@/Components/UI/Toast.vue';
import { useThemeStore } from '@/Stores/theme';
import { router } from '@inertiajs/vue3';

const themeStore = useThemeStore();
const sidebarCollapsed = ref(false);
const mobileMenuOpen = ref(false);

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
    localStorage.setItem('sidebar-collapsed', sidebarCollapsed.value);
}

function openMobileMenu() {
    mobileMenuOpen.value = true;
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}

// Close mobile menu on page navigation
const unregisterNavigate = router.on('navigate', () => {
    mobileMenuOpen.value = false;
});

const isLoading = ref(false);
let loadingTimeout = null;

const unregisterStart = router.on('start', () => {
    if (loadingTimeout) clearTimeout(loadingTimeout);
    loadingTimeout = setTimeout(() => {
        isLoading.value = true;
    }, 150);
});

const unregisterFinish = router.on('finish', () => {
    if (loadingTimeout) {
        clearTimeout(loadingTimeout);
        loadingTimeout = null;
    }
    isLoading.value = false;
});

onMounted(() => {
    themeStore.init();
    sidebarCollapsed.value = localStorage.getItem('sidebar-collapsed') === 'true';
});

onUnmounted(() => {
    unregisterNavigate();
    unregisterStart();
    unregisterFinish();
    if (loadingTimeout) clearTimeout(loadingTimeout);
});
</script>

<template>
    <div class="min-h-screen bg-background transition-theme">
        <Sidebar
            :collapsed="sidebarCollapsed"
            :mobile-open="mobileMenuOpen"
            @toggle="toggleSidebar"
            @close-mobile="closeMobileMenu"
        />
        <Header
            :collapsed="sidebarCollapsed"
            @open-mobile="openMobileMenu"
        />

        <main
            :class="[
                'pt-[72px] min-h-screen transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-20' : 'lg:ml-72',
            ]"
        >
            <div v-if="isLoading" class="p-4 md:p-6 space-y-6">
                <!-- Page Title Skeleton -->
                <div class="space-y-2 max-w-[200px]">
                    <div class="h-8 w-3/4 skeleton"></div>
                    <div class="h-4 w-full skeleton"></div>
                </div>

                <!-- 4 Metrics Cards Skeleton -->
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div v-for="i in 4" :key="i" class="glass-card p-5 h-[148px] flex flex-col justify-between">
                        <div class="flex flex-col gap-3">
                            <div class="h-6 w-6 skeleton"></div>
                            <div class="h-8 w-1/2 skeleton mt-1"></div>
                        </div>
                        <div class="space-y-1.5 mt-3">
                            <div class="h-2.5 w-1/4 skeleton"></div>
                            <div class="h-3 w-1/2 skeleton"></div>
                        </div>
                    </div>
                </div>

                <!-- Main Grid Content Skeleton -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <div class="lg:col-span-2 space-y-6">
                        <div class="glass-card p-6 h-[300px]">
                            <div class="h-5 w-1/4 skeleton mb-6"></div>
                            <div class="h-[200px] w-full skeleton"></div>
                        </div>
                        <div class="glass-card p-6 h-[400px]">
                            <div class="h-5 w-1/4 skeleton mb-6"></div>
                            <div class="h-[300px] w-full skeleton"></div>
                        </div>
                    </div>
                    <div class="space-y-6">
                        <div class="glass-card p-6 h-[300px]">
                            <div class="h-5 w-1/4 skeleton mb-6"></div>
                            <div class="h-[200px] w-full skeleton"></div>
                        </div>
                        <div class="glass-card p-6 h-[400px]">
                            <div class="h-5 w-1/4 skeleton mb-6"></div>
                            <div class="h-[300px] w-full skeleton"></div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-else class="animate-fade-in">
                <slot />
            </div>
        </main>

        <Toast />
    </div>
</template>
