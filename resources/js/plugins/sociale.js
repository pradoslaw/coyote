import axios from "axios";
import NavAuth from '../components/nav-auth.vue';
import VueSearchbar from '../components/searchbar/searchbar.vue';
import {default as setToken} from "../libs/csrf";
import store from '../store';
import {createVueApp} from '../vue';

createVueApp('Searchbar', '#js-searchbar', {
  components: {'vue-searchbar': VueSearchbar},
});

const el = document.getElementById('nav-auth');

if (el !== null) {
  store.commit('inbox/SET_COUNT', store.state.user.user.pm_unread);
  store.commit('notifications/init', {notifications: null, count: store.state.user.user.notifications_unread});
  createVueApp('NavAuth', '#nav-auth', NavAuth);
}

/**
 * setInterval() handler. Retrieves CSRF token
 *
 * @returns {Promise<void>}
 */
const sessionHandler = () => axios.get('/ping', {errorHandle: false}).then(response => setToken(response.data));

/**
 * Keep session alive every 4 minutes
 *
 * @returns {number}
 */
const keepSessionAlive = () => setInterval(sessionHandler, 4 * 60 * 1000);

let timer = keepSessionAlive();

window.addEventListener('online', () => {
  sessionHandler();

  timer = keepSessionAlive();
});

window.addEventListener('offline', () => clearInterval(timer));
