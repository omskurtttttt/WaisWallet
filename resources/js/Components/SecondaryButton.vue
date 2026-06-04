<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex h-11 items-center justify-center rounded-xl bg-secondary px-6 text-sm font-semibold text-secondary-foreground border border-border transition-all duration-200 hover:bg-muted focus:outline-none focus:ring-2 focus:ring-primary/30 disabled:opacity-50 cursor-pointer"
    >
        <slot />
    </button>
</template>
