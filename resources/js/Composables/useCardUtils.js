export function useCardUtils() {
    const getAccountCategory = (brand) => {
        const brandLower = brand?.toLowerCase();
        if (['gcash', 'maya', 'grabpay', 'coins.ph'].includes(brandLower)) {
            return {
                name: 'E-Wallet',
                color: '#8c5cff', // Violet
                icon: 'wallet',
                colorClass: 'bg-[#8c5cff]/10 text-[#8c5cff]',
                badgeClass: 'bg-[#8c5cff]/10 text-[#8c5cff]'
            };
        } else if (['bpi', 'bdo', 'metrobank', 'unionbank'].includes(brandLower)) {
            return {
                name: 'Bank Account',
                color: '#38bdf8', // Blue
                icon: 'bank',
                colorClass: 'bg-[#38bdf8]/10 text-[#38bdf8]',
                badgeClass: 'bg-[#38bdf8]/10 text-[#38bdf8]'
            };
        } else if (brandLower === 'cash') {
            return {
                name: 'Cash',
                color: '#3ee08f', // Green
                icon: 'cash',
                colorClass: 'bg-[#3ee08f]/10 text-[#3ee08f]',
                badgeClass: 'bg-[#3ee08f]/10 text-[#3ee08f]'
            };
        } else {
            return {
                name: 'Credit Card',
                color: '#ff477e', // Red
                icon: 'card',
                colorClass: 'bg-[#ff477e]/10 text-[#ff477e]',
                badgeClass: 'bg-[#ff477e]/10 text-[#ff477e]'
            };
        }
    };

    return {
        getAccountCategory
    };
}
