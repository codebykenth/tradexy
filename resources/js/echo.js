import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const meta = (name) => document.querySelector(`meta[name="${name}"]`)?.content;

const key = meta('pusher-app-key') || import.meta.env.VITE_PUSHER_APP_KEY;
const cluster = meta('pusher-app-cluster') || import.meta.env.VITE_PUSHER_APP_CLUSTER;

if (meta('realtime-enabled') === '1' && key && cluster) {
    window.Echo = new Echo({
        broadcaster: 'pusher',
        key,
        cluster,
        forceTLS: true,
    });
}
