<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Analytics" />
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-foreground">Analytics</h1>
            <p class="mt-2 text-muted-foreground">Analyze your financial trends.</p>
        </div>
    </AuthenticatedLayout>
</template>
