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
    
    <div class="min-h-screen bg-background text-foreground transition-theme font-sans antialiased relative overflow-hidden">
        
        <!-- Background Grid Pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(to_right,var(--border)_1px,transparent_1px),linear-gradient(to_bottom,var(--border)_1px,transparent_1px)] bg-[size:4rem_4rem] [mask-image:radial-gradient(ellipse_60%_50%_at_50%_0%,#000_70%,transparent_100%)] opacity-30 pointer-events-none"></div>

        <!-- Glowing Accent blobs -->
        <div class="absolute -top-[10%] left-[5%] w-[35rem] h-[35rem] rounded-full bg-primary/10 blur-[100px] pointer-events-none"></div>
        <div class="absolute top-[40%] right-[-5%] w-[40rem] h-[40rem] rounded-full bg-accent/20 blur-[120px] pointer-events-none"></div>

        <!-- Navigation Header -->
        <header class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="flex h-20 items-center justify-between border-b border-border/50">
                
                <!-- Logo & Brand -->
                <div class="flex items-center gap-2">
                    <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-primary/10 text-primary">
                        <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                            <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                            <path d="M18 12a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4v-6Z" />
                            <circle cx="20" cy="15" r="1" fill="currentColor" />
                        </svg>
                    </div>
                    <span class="text-xl font-bold tracking-tight bg-gradient-to-r from-foreground to-foreground/80 bg-clip-text text-transparent">
                        WaisWallet
                    </span>
                </div>

                <!-- Right Nav Actions -->
                <div class="flex items-center gap-4">
                    <!-- Light / Dark Mode Toggle -->
                    <button
                        @click="themeStore.toggle()"
                        class="flex h-10 w-10 items-center justify-center rounded-xl border border-border/60 hover:bg-muted text-muted-foreground hover:text-foreground transition-all duration-200"
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

                    <!-- Authentication Routing -->
                    <nav v-if="canLogin" class="flex items-center gap-3">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-5 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/95 transition-all duration-200"
                        >
                            Go to Dashboard
                        </Link>
                        <template v-else>
                            <Link
                                :href="route('login')"
                                class="inline-flex h-10 items-center justify-center rounded-xl border border-border/80 px-4 text-sm font-semibold text-foreground hover:bg-muted transition-all duration-200"
                            >
                                Log in
                            </Link>
                            <Link
                                v-if="canRegister"
                                :href="route('register')"
                                class="inline-flex h-10 items-center justify-center rounded-xl bg-primary px-4 text-sm font-semibold text-primary-foreground shadow-sm hover:bg-primary/95 transition-all duration-200"
                            >
                                Register
                            </Link>
                        </template>
                    </nav>
                </div>
            </div>
        </header>

        <!-- Hero Section -->
        <main class="relative z-10 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 pt-12 pb-24 lg:pt-20">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 lg:gap-8 items-center">
                
                <!-- Left: Headings and Description -->
                <div class="lg:col-span-5 text-center lg:text-left space-y-6">
                    <div class="inline-flex items-center gap-2 rounded-full border border-primary/20 bg-primary/5 px-4 py-1.5 text-xs font-semibold text-primary">
                        <span class="flex h-2 w-2 rounded-full bg-primary animate-pulse"></span>
                        Manual personal finance manager
                    </div>
                    
                    <h1 class="text-4xl sm:text-5xl font-extrabold tracking-tight leading-none text-foreground">
                        Wise Decisions for Your <span class="bg-gradient-to-r from-primary to-accent-foreground bg-clip-text text-transparent">Wealth</span>.
                    </h1>
                    
                    <p class="text-base sm:text-lg text-muted-foreground max-w-xl mx-auto lg:mx-0">
                        Take full control of your financial future. WaisWallet is a premium personal finance dashboard built to track your income, manage expenses, set budgets, and achieve savings goals.
                    </p>

                    <div class="flex flex-col sm:flex-row gap-3 justify-center lg:justify-start">
                        <Link
                            v-if="$page.props.auth.user"
                            :href="route('dashboard')"
                            class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-base font-semibold text-primary-foreground shadow-md hover:bg-primary/95 hover:shadow-lg transition-all duration-200"
                        >
                            Open Dashboard
                        </Link>
                        <Link
                            v-else
                            :href="route('register')"
                            class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-6 text-base font-semibold text-primary-foreground shadow-md hover:bg-primary/95 hover:shadow-lg transition-all duration-200"
                        >
                            Start Tracking Free
                        </Link>
                        <a
                            href="#features"
                            class="inline-flex h-12 items-center justify-center rounded-xl border border-border/80 bg-background/50 px-6 text-base font-semibold text-foreground hover:bg-muted transition-all duration-200"
                        >
                            Explore Features
                        </a>
                    </div>

                    <!-- Trust Stats Bar -->
                    <div class="pt-6 border-t border-border/50 grid grid-cols-3 gap-4 text-center lg:text-left">
                        <div>
                            <p class="text-2xl font-bold text-foreground font-financial">100%</p>
                            <p class="text-xs text-muted-foreground">Manual Control</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground font-financial">0</p>
                            <p class="text-xs text-muted-foreground">Bank Sync Bloat</p>
                        </div>
                        <div>
                            <p class="text-2xl font-bold text-foreground font-financial">Secure</p>
                            <p class="text-xs text-muted-foreground">Postgres Storage</p>
                        </div>
                    </div>
                </div>

                <!-- Right: Mockup Dashboard -->
                <div class="lg:col-span-7 relative">
                    <!-- Ambient Glow behind mockup -->
                    <div class="absolute -inset-1 rounded-2xl bg-gradient-to-tr from-primary to-accent opacity-20 blur-xl"></div>
                    
                    <!-- Mockup Container -->
                    <div class="relative rounded-2xl border border-border bg-card/60 backdrop-blur-xl card-shadow overflow-hidden p-4 sm:p-5 transition-transform duration-300 hover:scale-[1.01]">
                        <!-- Window header bar -->
                        <div class="flex items-center justify-between pb-4 border-b border-border/50">
                            <div class="flex items-center gap-1.5">
                                <span class="h-3 w-3 rounded-full bg-destructive/80"></span>
                                <span class="h-3 w-3 rounded-full bg-chart-3/80"></span>
                                <span class="h-3 w-3 rounded-full bg-chart-1/80"></span>
                            </div>
                            <div class="text-xs text-muted-foreground font-mono bg-muted/60 px-3 py-1 rounded-md">
                                app.waiswallet.local/dashboard
                            </div>
                            <div class="w-12"></div>
                        </div>

                        <!-- Mockup App Content -->
                        <div class="pt-4 grid grid-cols-1 sm:grid-cols-4 gap-4">
                            <!-- Dashboard Mini Sidebar (left) -->
                            <div class="hidden sm:flex flex-col gap-2.5 col-span-1 border-r border-border/50 pr-4">
                                <div class="h-7 bg-primary/10 text-primary rounded-lg flex items-center px-2.5 text-xs font-semibold gap-1.5">
                                    <span class="h-1.5 w-1.5 rounded-full bg-primary"></span> Dashboard
                                </div>
                                <div class="h-7 text-muted-foreground hover:text-foreground rounded-lg flex items-center px-2.5 text-xs font-medium">
                                    Transactions
                                </div>
                                <div class="h-7 text-muted-foreground hover:text-foreground rounded-lg flex items-center px-2.5 text-xs font-medium">
                                    Cards Wallet
                                </div>
                                <div class="h-7 text-muted-foreground hover:text-foreground rounded-lg flex items-center px-2.5 text-xs font-medium">
                                    Savings Goals
                                </div>
                                <div class="h-7 text-muted-foreground hover:text-foreground rounded-lg flex items-center px-2.5 text-xs font-medium">
                                    Analytics
                                </div>
                            </div>

                            <!-- Dashboard Main Content (right) -->
                            <div class="col-span-1 sm:col-span-3 space-y-4">
                                <!-- Cards Row -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div class="p-3 border border-border/60 bg-muted/30 rounded-xl">
                                        <p class="text-[10px] text-muted-foreground font-medium">Total Balance</p>
                                        <p class="text-base font-bold text-foreground font-financial mt-0.5">$14,240.50</p>
                                        <p class="text-[9px] text-chart-1 font-semibold flex items-center gap-0.5 mt-1">
                                            <span class="inline-block">▲</span> +2.4%
                                        </p>
                                    </div>
                                    <div class="p-3 border border-border/60 bg-muted/30 rounded-xl">
                                        <p class="text-[10px] text-muted-foreground font-medium">Monthly Savings</p>
                                        <p class="text-base font-bold text-foreground font-financial mt-0.5">$3,599.50</p>
                                        <p class="text-[9px] text-chart-1 font-semibold flex items-center gap-0.5 mt-1">
                                            <span class="inline-block">▲</span> +12.1%
                                        </p>
                                    </div>
                                </div>

                                <!-- Chart & Transactions -->
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <!-- Mini Chart Mockup -->
                                    <div class="p-3 border border-border/60 bg-muted/30 rounded-xl space-y-2">
                                        <p class="text-[10px] font-semibold text-foreground">Budget Allocation</p>
                                        <div class="h-24 flex items-center justify-center relative">
                                            <!-- Simple Donut CSS graphic -->
                                            <div class="h-16 w-16 rounded-full border-4 border-primary border-t-chart-3 border-l-chart-1 border-r-chart-4 flex items-center justify-center">
                                                <div class="text-[8px] font-bold text-center leading-none">
                                                    $1.8K<br/><span class="text-[6px] text-muted-foreground font-medium">spent</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Recent Transactions List Mockup -->
                                    <div class="p-3 border border-border/60 bg-muted/30 rounded-xl space-y-2">
                                        <p class="text-[10px] font-semibold text-foreground">Recent Transactions</p>
                                        <div class="space-y-1.5">
                                            <div class="flex items-center justify-between text-[9px]">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="h-5 w-5 rounded-full bg-chart-2/15 text-chart-2 flex items-center justify-center font-bold text-[8px]">AD</span>
                                                    <div>
                                                        <p class="font-semibold leading-tight text-foreground">Acme Corp</p>
                                                        <p class="text-[7px] text-muted-foreground">May 24, 2026</p>
                                                    </div>
                                                </div>
                                                <span class="font-bold text-chart-1 font-financial">+$3,500.00</span>
                                            </div>
                                            <div class="flex items-center justify-between text-[9px]">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="h-5 w-5 rounded-full bg-destructive/15 text-destructive flex items-center justify-center font-bold text-[8px]">ST</span>
                                                    <div>
                                                        <p class="font-semibold leading-tight text-foreground">Starbucks</p>
                                                        <p class="text-[7px] text-muted-foreground">May 23, 2026</p>
                                                    </div>
                                                </div>
                                                <span class="font-bold text-foreground font-financial">-$12.40</span>
                                            </div>
                                            <div class="flex items-center justify-between text-[9px]">
                                                <div class="flex items-center gap-1.5">
                                                    <span class="h-5 w-5 rounded-full bg-chart-4/15 text-chart-4 flex items-center justify-center font-bold text-[8px]">AM</span>
                                                    <div>
                                                        <p class="font-semibold leading-tight text-foreground">Amazon</p>
                                                        <p class="text-[7px] text-muted-foreground">May 22, 2026</p>
                                                    </div>
                                                </div>
                                                <span class="font-bold text-foreground font-financial">-$89.90</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </main>

        <!-- Features Grid Section -->
        <section id="features" class="relative z-10 border-t border-border bg-muted/20 py-20">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                
                <!-- Section Header -->
                <div class="text-center max-w-3xl mx-auto mb-16 space-y-4">
                    <h2 class="text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                        Everything you need to master your money
                    </h2>
                    <p class="text-base sm:text-lg text-muted-foreground">
                        Simplify manual tracking, monitor savings growth, allocate budgets, and examine spending reports inside one seamless system.
                    </p>
                </div>

                <!-- Feature Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                    
                    <!-- Feature 1: Core Dashboard -->
                    <div class="glass-card p-6 space-y-4 hover:shadow-lg transition-all duration-200">
                        <div class="h-12 w-12 rounded-xl bg-primary/10 text-primary flex items-center justify-center">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="3" width="7" height="9" />
                                <rect x="14" y="3" width="7" height="5" />
                                <rect x="14" y="12" width="7" height="9" />
                                <rect x="3" y="16" width="7" height="5" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground">Central Dashboard</h3>
                        <p class="text-sm text-muted-foreground">
                            View balance sheets, total monthly income, expenses, and savings at a single glance with progress metrics compared to your previous month.
                        </p>
                    </div>

                    <!-- Feature 2: Transactions -->
                    <div class="glass-card p-6 space-y-4 hover:shadow-lg transition-all duration-200">
                        <div class="h-12 w-12 rounded-xl bg-chart-3/10 text-chart-3 flex items-center justify-center">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="m12 3-1.912 5.886a1 1 0 0 1-.95.688H3l4.9 3.56a1 1 0 0 1 .36 1.12L6.35 20.14l4.9-3.56a1 1 0 0 1 1.18 0l4.9 3.56-1.91-5.886a1 1 0 0 1 .36-1.12L21 9.574h-6.138a1 1 0 0 1-.95-.688Z" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground">Transaction Registry</h3>
                        <p class="text-sm text-muted-foreground">
                            Log all transactions. Filter by date, category, status, and transaction type with complete pagination and server-side searching.
                        </p>
                    </div>

                    <!-- Feature 3: Card Wallet -->
                    <div class="glass-card p-6 space-y-4 hover:shadow-lg transition-all duration-200">
                        <div class="h-12 w-12 rounded-xl bg-chart-4/10 text-chart-4 flex items-center justify-center">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="2" y="5" width="20" height="14" rx="2" />
                                <line x1="2" y1="10" x2="22" y2="10" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground">Digital Wallet</h3>
                        <p class="text-sm text-muted-foreground">
                            Organize credit, debit, or checking cards with brand detection, custom nicknames, expiry tracking, and contactless indicator overlays.
                        </p>
                    </div>

                    <!-- Feature 4: Budget Charts -->
                    <div class="glass-card p-6 space-y-4 hover:shadow-lg transition-all duration-200">
                        <div class="h-12 w-12 rounded-xl bg-chart-1/10 text-chart-1 flex items-center justify-center">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <path d="M12 2a15.3 15.3 0 0 1 4 10H12V2Z" />
                                <path d="M12 12v10" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground">Budget Allocation</h3>
                        <p class="text-sm text-muted-foreground">
                            Allocate money to specific categories like Housing, Travelling, Food, and Entertainment. Track real-time limit indicators.
                        </p>
                    </div>

                    <!-- Feature 5: Savings Goals -->
                    <div class="glass-card p-6 space-y-4 hover:shadow-lg transition-all duration-200">
                        <div class="h-12 w-12 rounded-xl bg-chart-2/10 text-chart-2 flex items-center justify-center">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="12" cy="12" r="10" />
                                <circle cx="12" cy="12" r="6" />
                                <circle cx="12" cy="12" r="2" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground">Target Goals</h3>
                        <p class="text-sm text-muted-foreground">
                            Set savings targets for emergency funds or purchases. Track progress percentages, set target deadlines, and fund them step by step.
                        </p>
                    </div>

                    <!-- Feature 6: Report Analysis -->
                    <div class="glass-card p-6 space-y-4 hover:shadow-lg transition-all duration-200">
                        <div class="h-12 w-12 rounded-xl bg-destructive/10 text-destructive flex items-center justify-center">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z" />
                                <polyline points="14 2 14 8 20 8" />
                                <line x1="16" y1="13" x2="8" y2="13" />
                                <line x1="16" y1="17" x2="8" y2="17" />
                                <polyline points="10 9 9 9 8 9" />
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-foreground">Monthly Summaries</h3>
                        <p class="text-sm text-muted-foreground">
                            Review deep reports indicating your top expenses categories, net monthly savings, total income, and number of active logs.
                        </p>
                    </div>

                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section class="relative z-10 py-24 mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="relative rounded-3xl border border-primary/20 bg-gradient-to-r from-primary/10 via-primary/5 to-accent/5 overflow-hidden p-8 sm:p-12 text-center space-y-6">
                <!-- Blob inside CTA -->
                <div class="absolute inset-0 bg-primary/5 blur-2xl rounded-full scale-75 -translate-y-12"></div>
                
                <h2 class="relative z-10 text-3xl font-bold tracking-tight text-foreground sm:text-4xl">
                    Ready to make wiser financial decisions?
                </h2>
                
                <p class="relative z-10 text-base sm:text-lg text-muted-foreground max-w-2xl mx-auto">
                    Create an account today and start tracking your wealth in a clean, high-performance, and secure interface.
                </p>

                <div class="relative z-10 flex justify-center gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-8 text-base font-semibold text-primary-foreground shadow-md hover:bg-primary/95 transition-all duration-200"
                    >
                        Go to Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('register')"
                            class="inline-flex h-12 items-center justify-center rounded-xl bg-primary px-8 text-base font-semibold text-primary-foreground shadow-md hover:bg-primary/95 transition-all duration-200"
                        >
                            Get Started Now
                        </Link>
                        <Link
                            :href="route('login')"
                            class="inline-flex h-12 items-center justify-center rounded-xl border border-border bg-background/60 px-8 text-base font-semibold text-foreground hover:bg-muted transition-all duration-200"
                        >
                            Sign In
                        </Link>
                    </template>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="relative z-10 border-t border-border/60 py-12 bg-background/50">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                <div class="flex items-center gap-2">
                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-primary/10 text-primary">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 12V8H6a2 2 0 0 1-2-2c0-1.1.9-2 2-2h12v4" />
                            <path d="M4 6v12c0 1.1.9 2 2 2h14v-4" />
                            <path d="M18 12a2 2 0 0 0-2 2v2a2 2 0 0 0 2 2h4v-6Z" />
                            <circle cx="20" cy="15" r="1" fill="currentColor" />
                        </svg>
                    </div>
                    <span class="text-sm font-bold tracking-tight text-foreground">
                        WaisWallet
                    </span>
                </div>
                
                <p class="text-xs text-muted-foreground text-center">
                    &copy; 2026 WaisWallet. All rights reserved. Managed with manual tracking.
                </p>

                <p class="text-xs text-muted-foreground font-mono">
                    Laravel v{{ laravelVersion }} (PHP v{{ phpVersion }})
                </p>
            </div>
        </footer>

    </div>
</template>
