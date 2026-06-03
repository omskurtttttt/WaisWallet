<script setup>
defineProps({
    title: {
        type: String,
        default: 'No data found'
    },
    description: {
        type: String,
        default: ''
    }
});
</script>

<template>
    <div class="flex flex-col items-center justify-center text-center p-8 md:p-12 animate-fade-in">
        <!-- Icon Area -->
        <div class="mb-4 text-muted-foreground/30 hover:text-muted-foreground/50 transition-colors duration-300">
            <slot name="icon">
                <!-- Default Folder/Box SVG -->
                <svg class="h-12 w-12 mx-auto" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 13.5h3.86a2.25 2.25 0 0 1 2.008 1.24l.885 1.77a2.25 2.25 0 0 0 2.007 1.24h1.98a2.25 2.25 0 0 0 2.007-1.24l.885-1.77a2.25 2.25 0 0 1 2.007-1.24h3.86m-18 0h18a2.25 2.25 0 0 1 2.25 2.25v4.25A2.25 2.25 0 0 1 18 21.75H6a2.25 2.25 0 0 1-2.25-2.25V15.75A2.25 2.25 0 0 1 2.25 13.5Zm2.25-9h15a2.25 2.25 0 0 1 2.25 2.25v3a2.25 2.25 0 0 1-2.25 2.25H4.5A2.25 2.25 0 0 1 2.25 9.75v-3A2.25 2.25 0 0 1 4.5 4.5Z" />
                </svg>
            </slot>
        </div>

        <!-- Title -->
        <h3 class="text-base font-bold text-foreground">{{ title }}</h3>
        
        <!-- Description -->
        <p v-if="description" class="mt-1 text-sm text-muted-foreground max-w-sm">
            {{ description }}
        </p>
        <p v-else-if="$slots.description" class="mt-1 text-sm text-muted-foreground max-w-sm">
            <slot name="description" />
        </p>

        <!-- Action / Button -->
        <div v-if="$slots.action" class="mt-5">
            <slot name="action" />
        </div>
    </div>
</template>
