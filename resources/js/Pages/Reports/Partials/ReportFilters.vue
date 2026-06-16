<script setup>
import CustomSelect from '@/Components/CustomSelect.vue';

const viewType = defineModel('viewType', { type: String, required: true });
const selectedMonth = defineModel('selectedMonth', { type: Number, required: true });
const selectedYear = defineModel('selectedYear', { type: Number, required: true });

defineProps({
    availableYears: {
        type: Array,
        required: true,
    },
});

const emit = defineEmits(['change']);

const months = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

const emitChange = () => {
    emit('change');
};
</script>

<template>
    <div class="glass-card p-4 print:hidden animate-slide-up flex flex-col md:flex-row gap-4 items-center justify-between relative z-20">
        <!-- Toggle Mode tabs -->
        <div class="flex bg-muted/60 p-1 rounded-xl w-full md:w-auto">
            <button 
                @click="viewType = 'monthly'"
                :class="[
                    'flex-1 md:flex-none px-6 py-2 text-sm font-medium rounded-lg transition-all',
                    viewType === 'monthly' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                ]"
            >
                Monthly
            </button>
            <button 
                @click="viewType = 'yearly'"
                :class="[
                    'flex-1 md:flex-none px-6 py-2 text-sm font-medium rounded-lg transition-all',
                    viewType === 'yearly' ? 'bg-card text-foreground shadow-sm' : 'text-muted-foreground hover:text-foreground'
                ]"
            >
                Yearly
            </button>
        </div>

        <!-- Select Filters -->
        <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3 w-full md:w-auto">
            <CustomSelect 
                v-if="viewType === 'monthly'"
                v-model="selectedMonth"
                :options="months"
                @change="emitChange"
                class="w-full sm:min-w-[150px] sm:w-auto"
            />

            <CustomSelect 
                v-model="selectedYear"
                :options="availableYears"
                @change="emitChange"
                class="w-full sm:min-w-[120px] sm:w-auto"
            />
        </div>
    </div>
</template>
