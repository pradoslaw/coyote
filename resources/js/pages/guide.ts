import Vue from 'vue';
import VuePost from '@/components/guide/post.vue';
import store from '@/store';
import { Guide, Paginator } from "@/types/models";
import { default as axiosErrorHandler } from '@/libs/axios-error-handler';

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

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
    'vue-post': VuePost
  },
  created() {
    store.commit('guides/init', { guide: window.guide });
  }
});
