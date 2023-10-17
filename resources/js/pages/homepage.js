import PerfectScrollbar from 'perfect-scrollbar';
import Vue from "vue";
import VueNotifications from "vue-notification";
import VueMicroblog from "../components/microblog/microblog";
import VuePagination from "../components/pagination.vue";
import store from "../store";
import {mapGetters} from "vuex";
import axios from 'axios';
import { default as LiveMixin } from './microblog/live';
import VueModals from "@/plugins/modals";
import VuePaste from '@/plugins/paste';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});
Vue.use(VueModals);
Vue.use(VuePaste, {url: '/assets'});

new Vue({
  el: '#js-microblog',
  delimiters: ['${', '}'],
  mixins: [ LiveMixin ],
  components: {
    'vue-microblog': VueMicroblog,
    'vue-pagination': VuePagination,
  },
  store,
  created() {
    if ('pagination' in window) {
      store.commit('microblogs/INIT', window.pagination);
    }

    store.commit('flags/init', window.flags);
  },
  mounted() {
    this.liveNotifications();
  },
  methods: {
    changePage(page) {
      window.location.href = `${window.location.href.split('?')[0]}?page=${page}`;
    },

    scrollToMicroblog(microblog) {
      window.location.hash = `#entry-${microblog.id}`;
    }
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs', 'currentPage', 'totalPages']),

    microblog() {
      return this.microblogs[Object.keys(this.microblogs)[0]];
    }
  }
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
