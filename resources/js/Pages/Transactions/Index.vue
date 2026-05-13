<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Transactions" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Transactions</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage your income and expenses.</p>
                </div>
                <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90 transition-all duration-200 shadow-md shadow-primary/25">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14" /><path d="M5 12h14" />
                    </svg>
                    Add Transaction
                </button>
            </div>

            <div class="glass-card p-5 animate-slide-up">
                <div class="flex items-center justify-center h-64 text-muted-foreground">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 mb-4 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6" />
                        </svg>
                        <p class="text-lg font-medium">Transaction list coming soon</p>
                        <p class="text-sm mt-1">Full CRUD with filters, search, and pagination</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
