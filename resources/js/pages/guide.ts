import Vue from 'vue';
import VuePost from '@/components/guide/post.vue';
import VueForm from '@/components/guide/form.vue';
import VueHeadline from '@/components/guide/headline.vue';
import VueComment from '@/components/comments/comment.vue';
import VueCommentForm from '@/components/comments/form.vue';
import VuePagination from '@/components/pagination.vue';
import store from '@/store';
import VuePaste from "../plugins/paste";
import { Guide, Paginator } from "@/types/models";
import { default as axiosErrorHandler } from '@/libs/axios-error-handler';
import VueNotifications from "vue-notification";
import VueModals from "@/plugins/modals";
import { mapGetters, mapState, mapMutations } from "vuex";

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

Vue.use(VuePaste, {url: '/assets'});
Vue.use(VueNotifications, {componentName: 'vue-notifications'});
Vue.use(VueModals);

declare global {
  interface Window {
    pagination: Paginator;
    guide: Guide;
  }
}

new Vue({
  el: '#js-homepage',
  store,
  delimiters: ['${', '}'],
  components: { 'vue-headline': VueHeadline, 'vue-pagination': VuePagination },
  created() {
    store.commit('guides/INIT_PAGINATION', window.pagination);
  },
  computed: {
    guides() {
      return store.state.guides.pagination.data;
    },

    ...mapGetters('guides', ['totalPages', 'currentPage'])
  }
});

new Vue({
  el: '#js-guide',
  store,
  delimiters: ['${', '}'],
  components: {
    'vue-post': VuePost,
    'vue-comment': VueComment,
    'vue-comment-form': VueCommentForm
  },
  created() {
    store.commit('guides/INIT', { guide: window.guide });
    store.commit('comments/INIT', window.guide?.comments);
  },
  mounted() {
    document.getElementById('js-skeleton')?.remove();
  },
  computed: {
    commentsCount() {
      return store.state.guides.guide.comments_count;
    },

    ...mapGetters('user', ['isAuthorized']),
    ...mapState('guides', ['guide']),
    ...mapState('comments', ['comments'])
  },
  watch: {
    comments(newValue) {
      if (!newValue) {
        return;
      }

      store.commit('guides/SET_COMMENTS_COUNT', { guide: this.guide, count: Object.keys(newValue).length });
    }
  }
});

new Vue({
  el: '#js-form',
  store,
  delimiters: ['${', '}'],
  components: { 'vue-form': VueForm },
  created() {
    if (document.getElementById('js-form')) {
      store.commit('guides/INIT', { guide: {} });
    }
  },

  computed: {
    defaultContent() {
      return {title: '', excerpt: '', tags: []}
    }
  }
});
