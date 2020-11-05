import Vue from 'vue';
import VueMicroblog from "../components/microblog/microblog.vue";
import VuePagination from '../components/pagination.vue';
import VueForm from '../components/microblog/form.vue';
import VueNotifications from 'vue-notification';
import store from '../store';
import { mapGetters } from 'vuex';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';
import { default as ws } from '../libs/realtime';
import {Microblog, Paginator, Flag} from "../types/models";
import Prism from "prismjs";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

declare global {
  interface Window {
    pagination: Paginator;
    microblog: Microblog;
    flags: Flag[] | undefined;
  }
}

interface Observer {
  update(microblog: Microblog): void;
}

class LiveNotification {
  private observers: Observer[] = [];

  attach(observer: Observer) {
    this.observers.push(observer);
  }

  notify(microblog: Microblog) {
    for (const observer of this.observers) {
      observer.update(microblog);
    }
  }
}

class UpdateMicroblog implements Observer {
  update(microblog: Microblog) {
    const item = store.state.microblogs.data[microblog.id!];

    if (!item || item.is_editing) {
      return; // do not add new entries live (yet)
    }

    store.commit('microblogs/update', microblog);
  }
}

class UpdateComment implements Observer {
  update(comment: Microblog) {
    if (!comment.parent_id) {
      return;
    }

    const parent = store.state.microblogs.data[comment.parent_id];

    if (!parent || parent.comments[comment.id!]?.is_editing) {
      return;
    }

    store.commit(`microblogs/${comment.id! in parent.comments ? 'updateComment' : 'addComment'}`, { parent, comment });
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

    store.commit('flags/init', window.flags);
  },
  mounted() {
    const notification = new LiveNotification();

    notification.attach(new UpdateMicroblog());
    notification.attach(new UpdateComment());

    ws.subscribe('microblog').on('MicroblogSaved', (microblog: Microblog) => {
      // highlight not read text
      microblog.is_read = false;

      notification.notify(microblog);

      // highlight once again after saving
      this.$nextTick(() => Prism.highlightAll());
    });
  },
  methods: {
    changePage(page: number) {
      window.location.href = `${window.location.href.split('?')[0]}?page=${page}`;
    }
  },
  computed: {
    ...mapGetters('microblogs', ['microblogs', 'currentPage', 'totalPages']),

    microblog(): Microblog {
      return this.microblogs[Object.keys(this.microblogs)[0]];
    }
  }
});
