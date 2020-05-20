import Vue, { VNode } from "vue";
import VueTopic from '../components/forum/topic.vue';
import VuePagination from '../components/pagination.vue';
import VueTimeago from '../plugins/timeago';
import store from "../store";
import { Hit, Hits } from "../types/hit";
import { Model } from "../types/models";
import axios from 'axios';

type Sort = 'score' | 'date';

declare global {
  interface Window {
    hits: Hits;
    model: Model;
    query: string;
    sort: Sort;
    postsPerPage: number;
    categories: number[];
  }
}

Vue.use(VueTimeago);

Vue.component('vue-result-common', {
  props: {
    hits: {
      type: Array
    }
  },
  methods: {
    title(hit: Hit) {
      return hit.title ? hit.title : (hit.subject ? hit.subject : hit.text);
    }
  },
  template: `
    <ul id="search-results" class="list-unstyled">
      <li v-for="hit in hits">
        <h2 class="mt-4 mb-1"><a :href="hit.url" v-html="title(hit)"></a></h2>
        <p class="mb-1" v-html="hit.text"></p>

        <ul class="breadcrumb d-inline-flex p-0">
          <li v-for="breadcrumb in hit.breadcrumbs" class="breadcrumb-item"><a :href="breadcrumb.url">{{ breadcrumb.name }}</a></li>
        </ul>

        <vue-timeago :datetime="hit.created_at" class="text-muted"></vue-timeago>
      </li>
    </ul>`
})

Vue.component('vue-result-topic', {
  props: {
    hits: {
      type: Array
    }
  },
  components: { 'vue-topic': VueTopic },
  render: function(createElement) {
    let items: VNode[] = [];

    this.hits.forEach(hit => items.push(createElement('vue-topic', {props: {topic: hit, postsPerPage: window.postsPerPage, showCategoryName: true }})))

    return createElement('div', { class: 'card card-default card-topics' }, items)
  }
})

const Tabs = [
  { name: 'Wszystko', model: '' },
  { name: 'Forum', model: Model.Topic },
  { name: 'Praca', model: Model.Job },
  { name: 'Mikroblog', model: Model.Microblog },
]

new Vue({
  el: '#js-search',
  delimiters: ['${', '}'],
  data: {
    hits: window.hits,
    model: window.model,
    query: window.query,
    sort: window.sort,
    categories: window.categories,
    defaults: {
      sort: 'score'
    }
  },
  components: { 'vue-pagination': VuePagination },
  store,
  created() {
    store.commit('topics/init', window.hits.data || []);
  },

  methods: {
    getComponent() {
      return this.model ? `vue-result-${this.model.toLowerCase()}` : 'vue-result-common';
    },

    setSort(sort: Sort) {
      this.sort = sort;
      this.request();
    },

    searchLink(model: Model) {
      let params = { q: this.query, model };

      return `/Search?${new URLSearchParams(params).toString()}`;
    },

    request() {
      axios.get('/Search', {params: this.getParams()}).then(result => {
        this.hits = result.data;
      });
    },

    getParams() {
      let params = { q: this.query, model: this.model, sort: this.sort };

      return params;
    }
  },

  computed: {
    tabs() {
      return Tabs;
    },

    // getDefaultSort() {
    //   return this.sort || this.defaults.sort;
    // }
  }
  // watch: {
  //   sort() {
  //
  //   }
  // }
});
