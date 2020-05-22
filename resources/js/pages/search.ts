import Vue, { VNode } from "vue";
import VueTopic from '../components/forum/topic.vue';
import VuePagination from '../components/pagination.vue';
import VueTimeago from '../plugins/timeago';
import PerfectScrollbar from '../components/perfect-scrollbar';
import store from "../store";
import { Hit, Hits } from "../types/hit";
import { Model } from "../types/models";
import axios from 'axios';

type Sort = 'score' | 'date';

type ModelType = {
  [key in Model]: string;
};

type ForumType = {
  [key: number]: string;
};

enum SortOptions {
  'score' = 'Trafność',
  'date' = 'Data'
}

// @todo duplikat z searchbar.vue
const ModelOptions: ModelType = {
  [Model.Topic]: 'Wątki na forum',
  [Model.Job]: 'Oferty pracy',
  [Model.User]: 'Użytkownicy',
  [Model.Wiki]: 'Artykuły',
  [Model.Microblog]: 'Mikroblogi'
}

declare global {
  interface Window {
    hits: Hits;
    model: Model;
    query: string;
    sort: Sort;
    postsPerPage: number;
    categories: number[];
    forums: ForumType[];
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

new Vue({
  el: '#js-search',
  delimiters: ['${', '}'],
  data: {
    hits: window.hits,
    model: window.model,
    query: window.query,
    sort: window.sort,
    categories: window.categories,
    forums: window.forums,
    defaults: {
      sort: 'score'
    },
    sortOptions: SortOptions,
    modelOptions: ModelOptions
  },
  components: { 'vue-pagination': VuePagination, 'perfect-scrollbar': PerfectScrollbar },
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

    modelUrl(model?: Model) {
      let params = { ...this.requestParams, model };

      if (!model) {
        delete params['model'];
      }

      return this.getUrl(params);
    },

    sortUrl(sort: Sort) {
      return this.getUrl({ ...this.requestParams, sort });
    },

    request() {
      history.pushState(this.requestParams, '', this.getUrl(this.requestParams));

      axios.get('/Search', {params: this.requestParams}).then(result => {
        this.hits = result.data;
      });
    },

    getUrl(params: any) {
      return `/Search?${new URLSearchParams(params).toString()}`;
    },

    toggleCategory(id: number) {
      const index = this.categories.indexOf(id);

      index > -1 ? this.categories.splice(index) : this.categories.push(id);
    }
  },

  computed: {
    requestParams(): any {
      let params = { q: this.query, model: this.model, sort: this.sort };

      Object.keys(params).forEach(key => {
        if (!params[key]) {
          delete params[key];
        }
      })

      return params;
    },

    defaultSort(): Sort {
      return this.sort || this.defaults.sort;
    }
  }
});
