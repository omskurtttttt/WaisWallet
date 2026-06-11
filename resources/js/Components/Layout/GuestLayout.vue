<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="flex min-h-screen flex-col items-center justify-center bg-background p-4 sm:p-6 transition-theme relative overflow-hidden">
        <!-- Abstract Background Shapes -->
        <div class="absolute -top-[20%] -left-[10%] w-[50%] h-[50%] rounded-full bg-primary/5 blur-[120px] pointer-events-none"></div>
        <div class="absolute -bottom-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-chart-4/5 blur-[120px] pointer-events-none"></div>

        <div class="z-10 mb-8 animate-slide-up" style="animation-delay: 0.1s;">
            <Link href="/" class="flex items-center gap-3">
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-primary shadow-lg shadow-primary/25">
                    <svg class="h-6 w-6 text-primary-foreground" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12V7H5a2 2 0 0 1 0-4h14v4" />
                        <path d="M3 5v14a2 2 0 0 0 2 2h16v-5" />
                        <path d="M18 12a2 2 0 0 0 0 4h4v-4Z" />
                    </svg>
                </div>
                <span class="text-3xl font-bold tracking-tight text-foreground">
                    Wais<span class="text-primary">Wallet</span>
                </span>
            </Link>
        </div>

        <div class="z-10 w-full sm:max-w-md glass-card p-6 sm:p-8 animate-slide-up" style="animation-delay: 0.2s;">
            <slot />
        </div>
    </div>
</template>
