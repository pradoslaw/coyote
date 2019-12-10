import Config from '../libs/config';
import Vue from 'vue';
import VuePm from '../components/pms.vue';

const PmWrapper = Vue.extend(Object.assign(VuePm, {props: {counter: {default: Config.get('pm_unread')}}}));

const wrapper = new PmWrapper().$mount();
const el = document.getElementById('nav-auth');

if (el !== null) {
    el.appendChild(wrapper.$el);
}
