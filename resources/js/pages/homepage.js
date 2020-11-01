import PerfectScrollbar from 'perfect-scrollbar';
import Vue from "vue";
import VueNotifications from "vue-notification";
import VueMicroblog from "../components/microblog/microblog";
import store from "../store";
import {mapGetters} from "vuex";
import axios from 'axios';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

new Vue({
  el: '#js-microblog',
  delimiters: ['${', '}'],
  components: { 'vue-microblog': VueMicroblog },
  store,
  created() {
    Object.keys(window.microblogs).forEach(id => store.commit('microblogs/add', window.microblogs[id]));

    store.commit('flags/init', window.flags);
  },
  computed: mapGetters('microblogs', ['microblogs'])
});

function switchForumTab(index) {
  axios.post('/User/Settings/Ajax', {'homepage_mode': index});
}

function switchReputationTab(index) {
  axios.post('/User/Settings/Ajax', {'homepage_reputation': index});
}

(function () {
    new PerfectScrollbar(document.getElementById('stream'));

    let tabs = document.querySelectorAll('#forum-tabs .nav-link');

    for (let i = 0; i < tabs.length; i++) {
      tabs[i].addEventListener('click', () => switchForumTab(i));
    }

    tabs = document.querySelectorAll('#reputation-tabs .nav-item');

    for (let i = 0; i < tabs.length; i++) {
      tabs[i].addEventListener('click', () => switchReputationTab(i));
    }
})();
