import axios from 'axios';
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

import { ReverbClient } from './reverb-client.js';

window.Echo = new ReverbClient({
    key: import.meta.env.VITE_REVERB_APP_KEY,
    host: import.meta.env.VITE_REVERB_HOST,
    port: import.meta.env.VITE_REVERB_PORT ?? 8080,
    scheme: import.meta.env.VITE_REVERB_SCHEME ?? 'http',
    authEndpoint: '/broadcasting/auth',
});
