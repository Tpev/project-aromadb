// resources/js/bootstrap.js

import axios from 'axios';
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

// Initialiser Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Initialiser Laravel Echo
window.Pusher = Pusher;

// Ajoutez un log pour vérifier les valeurs des variables d'environnement
console.log('Pusher Key:', import.meta.env.VITE_PUSHER_APP_KEY);
console.log('Pusher Cluster:', import.meta.env.VITE_PUSHER_APP_CLUSTER);

window.Echo = new Echo({
    broadcaster: 'pusher',
    key: import.meta.env.VITE_PUSHER_APP_KEY,
    cluster: import.meta.env.VITE_PUSHER_APP_CLUSTER,
    forceTLS: true,
    encrypted: true,
    // logToConsole: true, // Décommentez pour le débogage
});
