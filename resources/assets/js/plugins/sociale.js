import Config from '../libs/config';
import Vue from 'vue';
import VueNotification from '../components/notifications/notifications.vue';
import VuePm from '../components/pm/inbox.vue';
import store from '../store';
import axios from "axios";

const el = document.getElementById('nav-auth');

if (el !== null) {
  store.commit('inbox/init', Config.get('pm_unread'));
  store.commit('notifications/init', {notifications: null, count: Config.get('notifications_unread')});

  const NotificationWrapper = Vue.extend(VueNotification);
  const PmWrapper = Vue.extend(VuePm);

  el.appendChild(new NotificationWrapper().$mount().$el);
  el.appendChild(new PmWrapper().$mount().$el);
}

setInterval(() => axios.get('/ping'), 4 * 60 * 1000);
