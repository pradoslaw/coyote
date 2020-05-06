import Vue from 'vue';
import VueMicroblog from "../components/microblog/microblog";
import VuePagination from '../components/pagination';
import VueForm from '../components/microblog/form';
import VueNotifications from 'vue-notification';
import store from '../store';
import { mapGetters } from 'vuex';
import axios from 'axios';

axios.interceptors.response.use(null, err => {
  let message = '';

  if (err.response) {
    if (err.response.data.errors) {
      const errors = err.response.data.errors;

      message = errors[Object.keys(errors)[0]][0];
    }
    else {
      message = err.response.data.message;
    }
  }
  else if (err.request) {
    message = err.request;
  }
  else {
    message = err.message;
  }

  Vue.notify({type: 'error', text: message});

  return Promise.reject(err);
});

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

new Vue({
  el: '#js-microblog',
  delimiters: ['${', '}'],
  components: { 'vue-microblog': VueMicroblog, 'vue-pagination': VuePagination, 'vue-form': VueForm },
  store,
  created() {
    store.commit('microblogs/init', { pagination: 'pagination' in window ? window.pagination : { data: window.microblogs } });
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
