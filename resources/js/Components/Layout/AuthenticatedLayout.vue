<script setup>
import { ref, onMounted } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import { useThemeStore } from '@/Stores/theme';

const themeStore = useThemeStore();
const sidebarCollapsed = ref(false);
const mobileMenuOpen = ref(false);

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}

function openMobileMenu() {
    mobileMenuOpen.value = true;
}

function closeMobileMenu() {
    mobileMenuOpen.value = false;
}

onMounted(() => {
    themeStore.init();
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
                'pt-16 min-h-screen transition-all duration-300',
                sidebarCollapsed ? 'lg:ml-[72px]' : 'lg:ml-[260px]',
            ]"
        >
            <div class="animate-fade-in">
                <slot />
            </div>
        </main>
    </div>
</template>
