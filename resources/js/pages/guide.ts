import Vue from 'vue';
import VuePost from '@/components/guide/post.vue';
import VueForm from '@/components/guide/form.vue';
import VueComment from '@/components/comment.vue';
import store from '@/store';
import VuePaste from "../plugins/paste";
import { Guide, Paginator } from "@/types/models";
import { default as axiosErrorHandler } from '@/libs/axios-error-handler';
import VueNotifications from "vue-notification";

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

Vue.use(VuePaste, {url: '/assets'});
Vue.use(VueNotifications, {componentName: 'vue-notifications'});

declare global {
  interface Window {
    pagination: Paginator;
    guide: Guide;
  }
}

new Vue({
  el: '#js-homepage',
  delimiters: ['${', '}'],
});

new Vue({
  el: '#js-guide',
  store,
  delimiters: ['${', '}'],
  components: {
    'vue-post': VuePost,
    'vue-comment': VueComment
  },
  created() {
    store.commit('guides/init', { guide: window.guide });
    store.commit('comments/INIT', window.guide.comments);
  },
  computed: {
    comments() {
      return store.state.comments;
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
      store.commit('guides/init', { guide: this.defaultContent });
    }
  },

  computed: {
    defaultContent() {
      return {title: '', excerpt: '', tags: []}
    }
  }
});
