<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Goals" />
    <AuthenticatedLayout>
        <div class="p-6">
            <h1 class="text-2xl font-bold text-foreground">Goals</h1>
            <p class="mt-2 text-muted-foreground">Track your savings goals.</p>
        </div>
    </AuthenticatedLayout>
</template>
