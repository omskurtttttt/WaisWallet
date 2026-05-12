<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Dashboard" />
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-foreground">Dashboard</h1>
            <p class="mt-2 text-muted-foreground">Welcome to WaisWallet — your personal finance dashboard.</p>
        </div>
    </AuthenticatedLayout>
</template>
