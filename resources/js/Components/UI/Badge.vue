<script setup>
import { computed } from 'vue';

const props = defineProps({
    variant: {
        type: String,
        default: 'primary',
        validator: (value) => ['primary', 'success', 'warning', 'danger', 'info', 'secondary'].includes(value)
    },
    size: {
        type: String,
        default: 'md',
        validator: (value) => ['sm', 'md', 'lg'].includes(value)
    }
});

const classes = computed(() => {
    const base = 'inline-flex items-center font-semibold rounded-full transition-colors duration-200';
    
    const sizes = {
        sm: 'px-2 py-0.5 text-[10px] leading-none',
        md: 'px-2.5 py-1 text-xs leading-none',
        lg: 'px-3 py-1.5 text-sm leading-none'
    };
    
    const variants = {
        primary: 'bg-primary/10 text-primary',
        success: 'bg-chart-1/10 text-chart-1',
        warning: 'bg-chart-3/10 text-chart-3',
        danger: 'bg-destructive/10 text-destructive',
        info: 'bg-accent-foreground/10 text-accent-foreground',
        secondary: 'bg-muted text-muted-foreground'
    };
    
    return `${base} ${sizes[props.size]} ${variants[props.variant]}`;
});
</script>

<template>
    <span :class="classes">
        <slot />
    </span>
</template>
