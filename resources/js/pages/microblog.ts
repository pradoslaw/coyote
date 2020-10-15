import Vue from 'vue';
import VueMicroblog from "../components/microblog/microblog.vue";
import VuePagination from '../components/pagination.vue';
import VueForm from '../components/microblog/form.vue';
import VueNotifications from 'vue-notification';
import store from '../store';
import { mapGetters } from 'vuex';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';
import { default as ws } from '../libs/realtime';
import {Microblog, Paginator} from "../types/models";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

declare global {
  interface Window {
    pagination: Paginator;
    microblog: Microblog;
  }
}

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
      store.commit('microblogs/add', window.microblog!);
    }
  },
  mounted() {
    ws.on('MicroblogSaved', (microblog: Microblog) => {
      if (microblog.parent_id) {
        const parent = store.state.microblogs.data[microblog.parent_id];

        this.liveComments(parent, microblog);
      }
      else {
        this.liveMicroblogs(microblog);
      }
    });
  },
  methods: {
    changePage(page: number) {
      window.location.href = `${window.location.href.split('?')[0]}?page=${page}`;
    },

    liveComments(parent: Microblog, comment: Microblog): void {
      if (!parent) {
        return;
      }

      store.commit(`microblogs/${comment.id! in parent.comments ? 'updateComment' : 'addComment'}`, { parent, comment });
    },

    liveMicroblogs(microblog): void {
      if (!this.exists(microblog.id)) {
        return; // do not add new entries
      }

      store.commit('microblogs/update', microblog);
    }
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs', 'currentPage', 'totalPages', 'exists']),

    microblog(): Microblog {
      return this.microblogs[Object.keys(this.microblogs)[0]];
    }
  }
});
