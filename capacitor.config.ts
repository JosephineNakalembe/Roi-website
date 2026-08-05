import type { CapacitorConfig } from '@capacitor/cli';

const config: CapacitorConfig = {
    appId: 'shop.roistore.app',
    appName: 'ROI Store',
    webDir: 'dist',
    server: {
        url: 'https://www.roistore.shop',
        androidScheme: 'https',
        cleartext: false,
    },
};

export default config;
