export function useIcons() {
    const getIconSvg = (iconName) => {
        switch (iconName) {
            case 'shopping-bag':
                return '<path d="M6 2 3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4Z"/><path d="M3 6h18"/><path d="M16 10a4 4 0 0 1-8 0"/>';
            case 'home':
                return '<path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/>';
            case 'car':
                return '<path d="M19 17h2c.6 0 1-.4 1-1v-3c0-.9-.7-1.7-1.5-1.9C18.7 10.6 16 10 16 10s-1.3-1.4-2.2-2.3c-.5-.4-1.1-.7-1.8-.7H5c-.6 0-1.1.4-1.4.9l-1.4 2.9A3.7 3.7 0 0 0 2 12v4c0 .6.4 1 1 1h2"/><path d="M5 17a2 2 0 1 0 4 0 2 2 0 1 0 -4 0"/><path d="M9 17h6"/><path d="M15 17a2 2 0 1 0 4 0 2 2 0 1 0 -4 0"/>';
            case 'utensils':
                return '<path d="M3 2v7c0 1.1.9 2 2 2h4a2 2 0 0 0 2-2V2"/><path d="M7 2v20"/><path d="M21 15V2v0a5 5 0 0 0-5 5v6c0 1.1.9 2 2 2h3Zm0 0v7"/>';
            case 'briefcase':
                return '<path d="M4 7h16c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V9c0-1.1.9-2 2-2z"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/>';
            case 'heart-pulse':
                return '<path d="M19 14c1.49-1.46 3-3.21 3-5.5A5.5 5.5 0 0 0 16.5 3c-1.76 0-3 .5-4.5 2-1.5-1.5-2.74-2-4.5-2A5.5 5.5 0 0 0 2 8.5c0 2.3 1.5 4.05 3 5.5l7 7Z"/><path d="M3.22 12H9.5l.5-1 2 4.5 2-7 1.5 3.5h5.27"/>';
            case 'graduation-cap':
                return '<path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/>';
            case 'coffee':
                return '<path d="M17 8h1a4 4 0 1 1 0 8h-1"/><path d="M3 8h14v9a4 4 0 0 1-4 4H7a4 4 0 0 1-4-4Z"/><path d="M6 2v2"/><path d="M10 2v2"/><path d="M14 2v2"/>';
            case 'shopping-cart':
                return '<path d="M7 21a1 1 0 1 0 2 0 1 1 0 1 0 -2 0"/><path d="M18 21a1 1 0 1 0 2 0 1 1 0 1 0 -2 0"/><path d="M2.05 2.05h2l2.66 12.42a2 2 0 0 0 2 1.58h9.78a2 2 0 0 0 1.95-1.57l1.65-7.43H5.12"/>';
            case 'film':
                return '<path d="M4.18 2h15.64C20.9 2 22 3.1 22 4.18v15.64c0 1.08-1.1 2.18-2.18 2.18H4.18C3.1 22 2 20.9 2 19.82V4.18C2 3.1 3.1 2 4.18 2z"/><path d="M7 2v20"/><path d="M17 2v20"/><path d="M2 12h20"/><path d="M2 7h5"/><path d="M2 17h5"/><path d="M17 17h5"/><path d="M17 7h5"/>';
            case 'zap':
                return '<path d="M13 2 3 14h9l-1 8 10-12h-9z"/>';
            case 'trending-up':
                return '<path d="m22 7-8.5 8.5-5-5L2 17"/><path d="M16 7h6v6"/>';
            case 'monitor':
                return '<path d="M4 3h16c1.1 0 2 .9 2 2v10c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V5c0-1.1.9-2 2-2z"/><path d="M8 21h8"/><path d="M12 17v4"/>';
            case 'gift':
                return '<path d="M12 7v14"/><path d="M20 11v8a2 2 0 0 1-2 2H6a2 2 0 0 1-2-2v-8"/><path d="M7.5 7a1 1 0 0 1 0-5A4.8 8 0 0 1 12 7a4.8 8 0 0 1 4.5-5 1 1 0 0 1 0 5"/><path d="M4 7h16a1 1 0 0 1 1 1v2a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1V8a1 1 0 0 1 1-1z"/>';
            case 'plane':
                return '<path d="M17.8 19.2L16 11l3.5-3.5C21 6 21.5 4 21 3c-1-.5-3 0-4.5 1.5L13 8 4.8 6.2c-.5-.1-.9.1-1.1.5l-.3.5c-.2.5-.1 1 .3 1.3L9 12l-2 3H4l-1 1 3 2 2 3 1-1v-3l3-2 3.5 5.3c.3.4.8.5 1.3.5z"/>';
            case 'tag':
                return '<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l7.3-7.3c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M6 6h.01"/>';
            default:
                // Fallback tag icon
                return '<path d="M12 2H2v10l9.29 9.29c.94.94 2.48.94 3.42 0l7.3-7.3c.94-.94.94-2.48 0-3.42L12 2Z"/><path d="M6 6h.01"/>';
        }
    };

    return {
        getIconSvg,
    };
}
