import Config from '../libs/config';
import Vue from 'vue';
import VueNotification from '../components/notifications.vue';
import VuePm from '../components/pms.vue';
import axios from "axios";

const NotificationWrapper = Vue.extend(Object.assign(VueNotification, {props: {counter: {default: Config.get('notifications_unread')}}}));
const PmWrapper = Vue.extend(Object.assign(VuePm, {props: {counter: {default: Config.get('pm_unread')}}}));

const el = document.getElementById('nav-auth');

if (el !== null) {
    el.appendChild(new NotificationWrapper().$mount().$el);
    el.appendChild(new PmWrapper().$mount().$el);
}

setInterval(() => axios.get('/ping'), 4 * 60 * 1000);
