import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { ReverbClient } from './reverb-client.js';

const reverbKey    = import.meta.env.VITE_REVERB_APP_KEY;
const reverbHost   = import.meta.env.VITE_REVERB_HOST;
const reverbPort   = import.meta.env.VITE_REVERB_PORT ?? 8080;
const reverbScheme = import.meta.env.VITE_REVERB_SCHEME ?? 'http';

if (reverbKey && reverbHost) {
    window.Echo = new ReverbClient({
        key:          reverbKey,
        host:         reverbHost,
        port:         reverbPort,
        scheme:       reverbScheme,
        authEndpoint: '/broadcasting/auth',
    });
} else {
    // Reverb not configured — provide a no-op stub so pages don't crash
    window.Echo = {
        channel:      () => ({ listen: () => ({}) }),
        private:      () => ({ listen: () => ({}) }),
        leave:        () => {},
        leaveChannel: () => {},
    };
}
