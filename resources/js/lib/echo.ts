import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

export type ReverbConfig = {
    key: string | null;
    host: string;
    port: number;
    scheme: string;
};

let echoInstance: Echo<'reverb'> | null = null;

export function getEcho(config: ReverbConfig): Echo<'reverb'> | null {
    if (!config.key) {
        return null;
    }

    if (echoInstance !== null) {
        return echoInstance;
    }

    window.Pusher = Pusher;

    echoInstance = new Echo({
        broadcaster: 'reverb',
        key: config.key,
        wsHost: config.host,
        wsPort: config.port,
        wssPort: config.port,
        forceTLS: config.scheme === 'https',
        enabledTransports: ['ws', 'wss'],
    });

    return echoInstance;
}

export function disconnectEcho(): void {
    echoInstance?.disconnect();
    echoInstance = null;
}
