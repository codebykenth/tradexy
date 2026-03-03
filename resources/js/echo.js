import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;

// Helper to pull meta tag content dynamically so we don't need distinct JS builds per environment
const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: meta('reverb-app-key') || import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: meta('reverb-host') || import.meta.env.VITE_REVERB_HOST,
    wsPort: meta('reverb-port') || import.meta.env.VITE_REVERB_PORT || 80,
    wssPort: meta('reverb-port') || import.meta.env.VITE_REVERB_PORT || 443,
    forceTLS: (meta('reverb-scheme') || import.meta.env.VITE_REVERB_SCHEME || 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
});
