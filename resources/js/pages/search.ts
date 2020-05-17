import Vue, { VNode } from "vue";
import VueTopic from '../components/forum/topic.vue';
import VuePagination from '../components/pagination.vue';
import store from "../store";
import { Hit, Hits } from "../types/hit";
import { Model } from "../types/models";

declare global {
  interface Window {
    hits: Hits;
    model: Model;
  }
}

Vue.component('vue-result-topic', {
  props: {
    hits: {
      type: Array
    }
  },
  components: { 'vue-topic': VueTopic },
  render: function(createElement) {
    let items: VNode[] = [];

    this.hits.forEach(hit => items.push(createElement('vue-topic', {props: {topic: hit}})))

    return createElement('div', { class: 'card card-default card-topics' }, items)
  }
})

new Vue({
  el: '#js-search',
  delimiters: ['${', '}'],
  data: { hits: window.hits, model: window.model },
  components: { 'vue-pagination': VuePagination },
  store,
  created() {
    store.commit('topics/init', window.hits.data || []);
  },

  methods: {
    getComponent() {
      return `vue-result-${this.model.toLowerCase()}`;
    }
  }

});
