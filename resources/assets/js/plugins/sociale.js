import Config from '../libs/config';
import Vue from 'vue';
import VueNotification from '../components/notifications/notifications.vue';
import VuePm from '../components/pm/inbox.vue';
import store from '../store';
import axios from "axios";
import { default as setToken } from "../libs/csrf";

const el = document.getElementById('nav-auth');

if (el !== null) {
  store.commit('inbox/init', Config.get('pm_unread'));
  store.commit('notifications/init', {notifications: null, count: Config.get('notifications_unread')});

  const NotificationWrapper = Vue.extend(VueNotification);
  const PmWrapper = Vue.extend(VuePm);

  el.appendChild(new NotificationWrapper().$mount().$el);
  el.appendChild(new PmWrapper().$mount().$el);
}

const sessionHandler = () => {
  axios.get('/ping').then(response => setToken(response.data));
};

const keepSessionAlive = () => {
  return setInterval(() => sessionHandler, 4 * 60 * 1000);
};

let timer = keepSessionAlive();

window.addEventListener('online', () => {
  sessionHandler();

  timer = keepSessionAlive();
});

window.addEventListener('offline', () => clearInterval(timer));
