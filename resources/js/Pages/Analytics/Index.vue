<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Analytics" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <div class="animate-fade-in">
                <h1 class="text-2xl font-bold text-foreground">Analytics</h1>
                <p class="mt-1 text-sm text-muted-foreground">Analyze your financial trends and patterns.</p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="glass-card p-5 animate-slide-up">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Spending Trends</h2>
                    <div class="flex items-center justify-center h-52 text-muted-foreground">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 mb-3 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <path d="M3 3v18h18" /><path d="m19 9-5 5-4-4-3 3" />
                            </svg>
                            <p class="text-sm">Line chart coming soon</p>
                        </div>
                    </div>
                </div>
                <div class="glass-card p-5 animate-slide-up">
                    <h2 class="text-lg font-semibold text-foreground mb-4">Category Breakdown</h2>
                    <div class="flex items-center justify-center h-52 text-muted-foreground">
                        <div class="text-center">
                            <svg class="mx-auto h-12 w-12 mb-3 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                <circle cx="12" cy="12" r="10" /><path d="M12 2a10 10 0 0 1 0 20" />
                            </svg>
                            <p class="text-sm">Pie chart coming soon</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
