import '@inertiajs/core';
import type { Auth } from './auth';

export type ReverbConfig = {
    key: string | null;
    host: string;
    port: number;
    scheme: string;
};

declare module '@inertiajs/core' {
    export interface InertiaConfig {
        sharedPageProps: {
            name: string;
            auth: Auth;
            sidebarOpen: boolean;
            reverb: ReverbConfig;
        };
    }
}

declare global {
    interface Window {
        Pusher: typeof import('pusher-js').default;
    }
}
