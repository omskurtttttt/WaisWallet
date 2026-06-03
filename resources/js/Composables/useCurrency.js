import { usePage } from '@inertiajs/vue3';

export function useCurrency() {
    const page = usePage();
    
    const currencyLocaleMap = {
        'PHP': 'en-PH',
        'USD': 'en-US',
        'EUR': 'de-DE',
        'GBP': 'en-GB',
        'JPY': 'ja-JP',
        'CAD': 'en-CA',
        'AUD': 'en-AU',
        'SGD': 'en-SG',
    };

    const formatMoney = (amountInCents, options = {}) => {
        const currency = page.props.auth?.user?.currency || 'PHP';
        const locale = currencyLocaleMap[currency.toUpperCase()] || 'en-US';
        return new Intl.NumberFormat(locale, {
            style: 'currency',
            currency: currency,
            ...options
        }).format((amountInCents || 0) / 100);
    };

    return {
        formatMoney,
    };
}
