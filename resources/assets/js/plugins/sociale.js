import Vue from 'vue';
import VueNotification from '../components/notifications/notifications.vue';
import VueSearchbar from '../components/forms/searchbar.vue';
import VuePm from '../components/pm/inbox.vue';
import store from '../store';
import axios from "axios";
import { default as setToken } from "../libs/csrf";

new Vue({
  el: '#js-searchbar',
  components: { 'vue-searchbar': VueSearchbar }
});

const el = document.getElementById('nav-auth');

if (el !== null) {
  store.commit('inbox/init', store.state.user.pm_unread);
  store.commit('notifications/init', {notifications: null, count: store.state.user.notifications_unread});

  const NotificationWrapper = Vue.extend(VueNotification);
  const PmWrapper = Vue.extend(VuePm);

  el.appendChild(new NotificationWrapper().$mount().$el);
  el.appendChild(new PmWrapper().$mount().$el);
}

/**
 * setInterval() handler. Retrieves CSRF token
 *
 * @returns {Promise<void>}
 */
const sessionHandler = () => axios.get('/ping').then(response => setToken(response.data));

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
