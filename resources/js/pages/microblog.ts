import Vue from 'vue';
import VueMicroblog from "../components/microblog/microblog.vue";
import VuePagination from '../components/pagination.vue';
import VueForm from '../components/microblog/form.vue';
import VueNotifications from 'vue-notification';
import VueModals from '../plugins/modals';
import VuePaste from '../plugins/paste.js';
import store from '../store';
import { mapGetters } from 'vuex';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';
import { Microblog, Paginator, Flag } from "@/types/models";
import { default as LiveMixin } from './microblog/live';
import VueAutosave from "../plugins/autosave";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});
Vue.use(VueModals);
Vue.use(VuePaste, {url: '/assets'});
Vue.use(VueAutosave);

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

declare global {
  interface Window {
    pagination: Paginator;
    microblog: Microblog;
    flags: Flag[] | undefined;
    popularTags: string[];
  }
}

new Vue({
  el: '#js-microblog',
  delimiters: ['${', '}'],
  components: { 'vue-microblog': VueMicroblog, 'vue-pagination': VuePagination, 'vue-form': VueForm },
  mixins: [ LiveMixin ],
  store,
  data() {
    return { popularTags: window.popularTags }
  },
  created() {
    if ('pagination' in window) {
      store.commit('microblogs/init', window.pagination);
    }

    if ('microblog' in window) {
      store.commit('microblogs/add', window.microblog!);
    }

    store.commit('flags/init', window.flags);
  },
  mounted() {
    // @ts-ignore
    this.liveNotifications();
  },
  methods: {
    changePage(page: number) {
      window.location.href = `${window.location.href.split('?')[0]}?page=${page}`;
    },

    scrollToMicroblog(microblog: Microblog) {
      window.location.hash = `#entry-${microblog.id}`;
    }
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs', 'currentPage', 'totalPages']),

    microblog(): Microblog {
      return this.microblogs[Object.keys(this.microblogs)[0]];
    }
  }
});
