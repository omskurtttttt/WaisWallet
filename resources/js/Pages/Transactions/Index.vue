<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Transactions" />
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-foreground">Transactions</h1>
            <p class="mt-2 text-muted-foreground">Manage your income and expenses.</p>
        </div>
    </AuthenticatedLayout>
</template>
