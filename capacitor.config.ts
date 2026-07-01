import type { CapacitorConfig } from '@capacitor/cli';

declare const process: {
    env: Record<string, string | undefined>;
};

const mobileServerUrl = process.env.CAPACITOR_SERVER_URL || 'http://10.0.2.2:8000/mobile';
const mobileServer = new URL(mobileServerUrl);

const config: CapacitorConfig = {
    appId: 'com.aromamade.pro',
    appName: 'AromaMade PRO',
    webDir: 'capacitor-web',
    server: {
        url: mobileServerUrl,
        cleartext: mobileServer.protocol === 'http:',
        allowNavigation: [
            mobileServer.hostname,
            'localhost',
            '127.0.0.1',
            '10.0.2.2',
        ],
    },
    android: {
        appendUserAgent: 'AromaMadeMobile',
        webContentsDebuggingEnabled: process.env.CAPACITOR_WEB_DEBUG === 'true',
    },
};

export default config;
