import axios from 'axios';
import Echo from 'laravel-echo';

// Initialize Axios
window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

