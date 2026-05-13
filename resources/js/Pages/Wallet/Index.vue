<script setup>
import AuthenticatedLayout from '@/Components/Layout/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';
</script>

<template>
    <Head title="Wallet" />
    <AuthenticatedLayout>
        <div class="p-4 md:p-6 space-y-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 animate-fade-in">
                <div>
                    <h1 class="text-2xl font-bold text-foreground">Wallet</h1>
                    <p class="mt-1 text-sm text-muted-foreground">Manage your payment cards.</p>
                </div>
                <button class="inline-flex items-center gap-2 rounded-xl bg-primary px-5 py-2.5 text-sm font-semibold text-primary-foreground hover:bg-primary/90 transition-all duration-200 shadow-md shadow-primary/25">
                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 5v14" /><path d="M5 12h14" />
                    </svg>
                    Add Card
                </button>
            </div>

            <div class="glass-card p-5 animate-slide-up">
                <div class="flex items-center justify-center h-64 text-muted-foreground">
                    <div class="text-center">
                        <svg class="mx-auto h-16 w-16 mb-4 opacity-20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                            <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                            <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                        </svg>
                        <p class="text-lg font-medium">Card management coming soon</p>
                        <p class="text-sm mt-1">Add and manage your payment cards securely</p>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
