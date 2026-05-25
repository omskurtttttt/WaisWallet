<script setup>
import { Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    data: {
        type: Array,
        required: true,
    }
});

const currentCardIndex = ref(0);

const nextCard = () => {
    if (props.data && props.data.length > 0) {
        currentCardIndex.value = (currentCardIndex.value + 1) % props.data.length;
    }
};

const prevCard = () => {
    if (props.data && props.data.length > 0) {
        currentCardIndex.value = (currentCardIndex.value - 1 + props.data.length) % props.data.length;
    }
};

const getBrandIcon = (brand) => {
    switch (brand?.toLowerCase()) {
        case 'visa':
            return 'VISA';
        case 'mastercard':
            return 'MC';
        case 'amex':
            return 'AMEX';
        default:
            return 'CARD';
    }
};
</script>

<template>
    <div class="glass-card p-5 animate-slide-up">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold text-foreground">My Cards</h2>
            <Link :href="route('wallet.index')" class="inline-flex items-center gap-1 text-sm font-medium text-primary hover:text-primary/80 transition-colors">
                <svg class="h-3 w-3 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round">
                    <line x1="12" y1="5" x2="12" y2="19"></line>
                    <line x1="5" y1="12" x2="19" y2="12"></line>
                </svg>
                <span>Add</span>
            </Link>
        </div>
        
        <div v-if="data && data.length > 0" class="relative">
            <!-- Card Carousel -->
            <div class="relative h-44 w-full rounded-2xl overflow-hidden transition-all duration-300 shadow-lg"
                 :style="{ 
                     background: `linear-gradient(135deg, ${data[currentCardIndex].color} 0%, ${data[currentCardIndex].color}dd 100%)` 
                 }">
                <!-- Abstract Design Elements -->
                <div class="absolute top-0 right-0 w-40 h-40 rounded-full bg-white/10 -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 rounded-full bg-white/5 translate-y-1/2 -translate-x-1/2"></div>
                
                <!-- Card Content -->
                <div class="relative z-10 flex flex-col justify-between h-full p-5 text-white">
                    <div class="flex justify-between items-start">
                        <!-- Chip Icon -->
                        <svg class="h-8 w-8 opacity-80" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                            <rect x="2" y="5" width="20" height="14" rx="2" />
                            <line x1="2" y1="10" x2="22" y2="10" />
                            <line x1="7" y1="5" x2="7" y2="10" />
                            <line x1="17" y1="5" x2="17" y2="10" />
                        </svg>
                        <span class="text-lg font-bold italic opacity-90">{{ getBrandIcon(data[currentCardIndex].brand) }}</span>
                    </div>
                    
                    <div>
                        <p class="text-lg font-financial tracking-widest mb-2 shadow-sm">
                            •••• •••• •••• {{ data[currentCardIndex].last_four }}
                        </p>
                        <div class="flex justify-between items-end">
                            <div>
                                <p class="text-[10px] uppercase opacity-70 tracking-wider">Card Name</p>
                                <p class="text-sm font-medium">{{ data[currentCardIndex].name }}</p>
                            </div>
                            <div class="text-right">
                                <p class="text-[10px] uppercase opacity-70 tracking-wider">Expires</p>
                                <p class="text-sm font-financial">{{ data[currentCardIndex].expires }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Navigation Dots -->
            <div v-if="data.length > 1" class="flex justify-center items-center gap-1.5 mt-4">
                <button 
                    v-for="(_, index) in data" 
                    :key="index"
                    @click="currentCardIndex = index"
                    :class="[
                        'h-1.5 rounded-full transition-all duration-300',
                        currentCardIndex === index ? 'w-4 bg-primary' : 'w-1.5 bg-muted-foreground/30 hover:bg-muted-foreground/50'
                    ]"
                    aria-label="Go to card"
                ></button>
            </div>
        </div>
        
        <div v-else class="h-44 rounded-2xl border-2 border-dashed border-border flex flex-col items-center justify-center text-muted-foreground bg-muted/10">
            <svg class="h-8 w-8 mb-2 opacity-50" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <rect width="20" height="14" x="2" y="5" rx="2" />
                <line x1="2" x2="22" y1="10" y2="10" />
            </svg>
            <p class="text-sm font-medium mb-1">No cards added</p>
            <Link :href="route('wallet.index')" class="text-xs text-primary hover:underline">Add your first card</Link>
        </div>
    </div>
</template>
