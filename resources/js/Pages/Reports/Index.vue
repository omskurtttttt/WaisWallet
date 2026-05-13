<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Reports" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <div class="animate-fade-in">
                <h1 class="text-2xl font-bold text-foreground">Reports</h1>
                <p class="mt-1 text-sm text-muted-foreground">View monthly and yearly financial summaries.</p>
            </div>

            <div class="glass-card p-5 animate-slide-up">
                <div class="flex items-center justify-center h-64 text-muted-foreground">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 mb-4 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M15 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7Z" />
                            <path d="M14 2v4a2 2 0 0 0 2 2h4" />
                            <path d="M10 18v-4" /><path d="M14 18v-6" />
                        </svg>
                        <p class="text-lg font-medium">Financial reports coming soon</p>
                        <p class="text-sm mt-1">Monthly and yearly summaries with export options</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
