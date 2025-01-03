import axios from 'axios';
import PerfectScrollbar from 'perfect-scrollbar';
import {mapGetters} from "vuex";

import VueMicroblog from "../components/microblog/microblog.vue";
import store from "../store/index.ts";
import {createVueAppNotifications} from '../vue';
import {default as LiveMixin} from './microblog/live';

createVueAppNotifications('Microblog', '#js-microblog', {
  delimiters: ['${', '}'],
  mixins: [LiveMixin],
  components: {'vue-microblog': VueMicroblog},
  store,
  created() {
    Object.keys(window.microblogs).forEach(id => store.commit('microblogs/ADD', window.microblogs[id]));

    store.commit('flags/init', window.flags);
  },
  mounted() {
    this.liveNotifications();
  },
  computed: mapGetters('microblogs', ['microblogs']),
});

function switchForumTab(index) {
  axios.post('/User/Settings/Ajax', {'homepage_mode': index});
}

function switchReputationTab(index) {
  axios.post('/User/Settings/Ajax', {'homepage_reputation': index});
}

(function () {
  new PerfectScrollbar(document.getElementById('stream'));

  const forumTabs = document.querySelectorAll('#forum-tabs .nav-link');
  for (let i = 0; i < forumTabs.length; i++) {
    forumTabs[i].addEventListener('click', () => switchForumTab(i));
  }

  const reputationTabs = document.querySelectorAll('#reputation-tabs .nav-item');
  for (let i = 0; i < reputationTabs.length; i++) {
    reputationTabs[i].addEventListener('click', () => switchReputationTab(i));
  }
})();
