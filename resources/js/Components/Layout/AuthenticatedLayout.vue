<script setup>
import { ref, onMounted } from 'vue';
import Sidebar from '@/Components/Layout/Sidebar.vue';
import Header from '@/Components/Layout/Header.vue';
import { useThemeStore } from '@/Stores/theme';

const themeStore = useThemeStore();
const sidebarCollapsed = ref(false);

function toggleSidebar() {
    sidebarCollapsed.value = !sidebarCollapsed.value;
}

onMounted(() => {
    themeStore.init();
});
</script>

<template>
    <div class="min-h-screen bg-background transition-theme">
        <Sidebar :collapsed="sidebarCollapsed" @toggle="toggleSidebar" />
        <Header :collapsed="sidebarCollapsed" />

        <main
            :class="[
                'pt-16 min-h-screen transition-all duration-300',
                sidebarCollapsed ? 'ml-[72px]' : 'ml-[260px]',
            ]"
        >
            <slot />
        </main>
    </div>
</template>
