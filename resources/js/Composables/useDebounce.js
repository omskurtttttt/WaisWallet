export function useDebounce() {
    const debounce = (fn, delay = 300) => {
        let timeoutId;
        return (...args) => {
            if (timeoutId) {
                clearTimeout(timeoutId);
            }
            timeoutId = setTimeout(() => {
                fn(...args);
            }, delay);
        };
    };

    return {
        debounce,
    };
}
