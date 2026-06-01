<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { useThemeStore } from '@/Stores/theme';
import { onMounted } from 'vue';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
});

const themeStore = useThemeStore();

onMounted(() => {
    themeStore.init();
});
</script>

<template>
    <Head title="Wise Decisions for Your Wealth" />

    <div class="min-h-screen bg-background text-foreground transition-colors duration-300 font-sans flex flex-col justify-between relative overflow-hidden">
        
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,var(--border)_1px,transparent_1px),linear-gradient(to_bottom,var(--border)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_40%,#000_70%,transparent_100%)] opacity-30 pointer-events-none"></div>

        <!-- Glowing Ambient blobs -->
        <div class="absolute -top-[10%] left-[10%] w-[30rem] h-[30rem] rounded-full bg-primary/10 blur-[120px] pointer-events-none"></div>
        <div class="absolute bottom-[10%] right-[10%] w-[30rem] h-[30rem] rounded-full bg-accent/20 blur-[120px] pointer-events-none"></div>

        <!-- Header -->
        <header class="relative z-10 mx-auto w-full max-w-7xl px-6 h-20 flex items-center justify-between">
            <div class="flex items-center gap-2">
                <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary shadow-sm">
                    <svg class="h-5.5 w-5.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                        <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                        <path d="M18 12a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4v-6Z" />
                    </svg>
                </div>
                <span class="text-lg font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-transparent">
                    WaisWallet
                </span>
            </div>

            <!-- Dark / Light Mode Toggle -->
            <button
                @click="themeStore.toggle()"
                class="flex h-10 w-10 items-center justify-center rounded-xl border border-border bg-card/50 hover:bg-muted text-muted-foreground hover:text-foreground transition-all duration-200 cursor-pointer shadow-sm"
                :title="themeStore.isDark ? 'Switch to light mode' : 'Switch to dark mode'"
            >
                <!-- Sun Icon (shown in dark mode) -->
                <svg v-if="themeStore.isDark" class="h-5 w-5 transition-transform duration-300 hover:rotate-45" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="4" />
                    <path d="M12 2v2" />
                    <path d="M12 20v2" />
                    <path d="m4.93 4.93 1.41 1.41" />
                    <path d="m17.66 17.66 1.41 1.41" />
                    <path d="M2 12h2" />
                    <path d="M20 12h2" />
                    <path d="m6.34 17.66-1.41 1.41" />
                    <path d="m19.07 4.93-1.41 1.41" />
                </svg>
                <!-- Moon Icon (shown in light mode) -->
                <svg v-else class="h-5 w-5 transition-transform duration-300 hover:-rotate-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z" />
                </svg>
            </button>
        </header>

        <!-- Main Content -->
        <main class="relative z-10 flex-1 flex items-center justify-center px-6 py-12">
            <div class="max-w-xl w-full text-center space-y-8">
                
                <!-- Tagline Badge -->
                <div class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-xs font-semibold text-primary select-none animate-fade-in">
                    <span class="flex h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                    Personal Finance & Wealth Tracker
                </div>

                <!-- Headline -->
                <div class="space-y-4">
                    <h1 class="text-4xl sm:text-5xl font-black tracking-tight leading-tight text-foreground">
                        Wise Decisions for<br/>
                        Your <span class="bg-gradient-to-r from-primary to-accent-foreground bg-clip-text text-transparent">Wealth</span>.
                    </h1>
                    <p class="text-base sm:text-lg text-muted-foreground max-w-md mx-auto">
                        Take full control of your financial destiny. Log income, track transactions, set savings targets, and review automated reports.
                    </p>
                </div>

                <!-- CTA Actions -->
                <div v-if="canLogin" class="flex flex-col sm:flex-row gap-3 justify-center max-w-sm mx-auto">
                    <template v-if="$page.props.auth.user">
                        <Link
                            :href="route('dashboard')"
                            class="flex-1 inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer"
                        >
                            Go to Dashboard
                        </Link>
                    </template>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="flex-1 inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-sm font-bold text-primary-foreground shadow-lg shadow-primary/25 hover:bg-primary/90 hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer"
                        >
                            Log In
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="flex-1 inline-flex h-12 items-center justify-center rounded-xl border border-border bg-card/65 backdrop-blur-md px-6 text-sm font-bold text-foreground hover:bg-muted hover:scale-[1.02] active:scale-[0.98] transition-all duration-200 cursor-pointer"
                        >
                            Register
                        </Link>
                    </template>
                </div>

                <!-- Minimal Info Grid -->
                <div class="pt-8 border-t border-border/50 grid grid-cols-3 gap-6 max-w-md mx-auto text-center">
                    <div>
                        <p class="text-lg font-extrabold text-foreground tracking-tight">100%</p>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-muted-foreground mt-0.5">Manual Control</p>
                    </div>
                    <div>
                        <p class="text-lg font-extrabold text-foreground tracking-tight">Zero</p>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-muted-foreground mt-0.5">Bank Sync Bloat</p>
                    </div>
                    <div>
                        <p class="text-lg font-extrabold text-foreground tracking-tight">Secure</p>
                        <p class="text-[10px] uppercase font-bold tracking-wider text-muted-foreground mt-0.5">Local DB</p>
                    </div>
                </div>

            </div>
        </main>

        <!-- Footer -->
        <footer class="relative z-10 mx-auto w-full max-w-7xl px-6 py-8 border-t border-border/30 flex flex-col sm:flex-row items-center justify-between gap-4 text-xs text-muted-foreground">
            <div class="flex items-center gap-2">
                <span class="font-bold text-foreground">WaisWallet</span>
                <span class="text-[10px] font-mono opacity-50">Laravel v{{ laravelVersion }}</span>
            </div>
            
            <p class="text-center sm:text-left">&copy; 2026 WaisWallet. All rights reserved.</p>
        </footer>

    </div>
</template>
