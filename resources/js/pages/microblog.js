import Vue from 'vue';
import VueMicroblog from "../components/microblog/microblog";
import VuePagination from '../components/pagination';
import VueForm from '../components/microblog/form';
import VueNotifications from 'vue-notification';
import store from '../store';
import { mapGetters } from 'vuex';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  el: '#js-microblog',
  delimiters: ['${', '}'],
  components: { 'vue-microblog': VueMicroblog, 'vue-pagination': VuePagination, 'vue-form': VueForm },
  store,
  created() {
    if ('pagination' in window) {
      store.commit('microblogs/init', window.pagination);
    }

    if ('microblog' in window) {
      store.commit('microblogs/add', window.microblog);
    }
  },
  methods: {
    changePage(page) {
      window.location.href = `${window.location.href.split('?')[0]}?page=${page}`;
    }
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs', 'currentPage', 'totalPages']),

    microblog() {
      return this.microblogs[Object.keys(this.microblogs)[0]];
    }
  }
});
