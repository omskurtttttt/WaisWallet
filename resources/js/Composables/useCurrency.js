import { usePage } from '@inertiajs/vue3';

export function useCurrency() {
    const page = usePage();
    
    const formatMoney = (amountInCents, options = {}) => {
        const currency = page.props.auth?.user?.currency || 'PHP';
        return new Intl.NumberFormat('en-US', {
            style: 'currency',
            currency: currency,
            ...options
        }).format((amountInCents || 0) / 100);
    };

    return {
        formatMoney,
    };
}
