<script setup>
import { ref, onMounted, onUnmounted } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import Toast from '@/Components/Toast.vue';
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

onMounted(() => {
    themeStore.init();
    sidebarCollapsed.value = localStorage.getItem('sidebar-collapsed') === 'true';
});

onUnmounted(() => {
    unregisterNavigate();
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
            <div class="animate-fade-in">
                <slot />
            </div>
        </main>

        <Toast />
    </div>
</template>
