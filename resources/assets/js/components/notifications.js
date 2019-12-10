import Config from '../libs/config';
import Vue from 'vue';
import VueNotification from '../components/notifications.vue';

const NotificationWrapper = Vue.extend(Object.assign(VueNotification, {props: {counter: {default: Config.get('notifications_unread')}}}));

const wrapper = new NotificationWrapper().$mount();
const el = document.getElementById('nav-auth');

if (el !== null) {
    el.appendChild(wrapper.$el);
}
