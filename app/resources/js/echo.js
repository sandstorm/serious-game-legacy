import Echo from 'laravel-echo';

import Pusher from 'pusher-js';
window.Pusher = Pusher;


const configMeta = document.querySelector('meta[name="app-config-js"]');

if (configMeta) {
    try {
        const config = JSON.parse(configMeta.getAttribute('content'));
        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: config.reverbAppKey,
            wsHost: window.location.hostname,
            wsPort: window.location.port,
            wssPort: window.location.port,
            forceTLS: window.location.protocol === 'https:',
            enabledTransports: ['ws', 'wss'],
        });
    } catch (error) {
        console.error('Failed to parse Echo configuration:', error);
    }
} else {
    console.error('Broadcast configuration not found');
}
