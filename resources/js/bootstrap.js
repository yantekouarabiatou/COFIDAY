import axios from 'axios';
window.axios = axios;
import Echo from 'laravel-echo';
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
window.Echo = new Echo({ ... });
