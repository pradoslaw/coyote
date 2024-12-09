import axios from 'axios';
import {h, VNode} from "vue";

import VueDropdownMenu from '../components/dropdown-menu.vue';
import VueAutocomplete from '../components/forms/autocomplete.vue';
import VueTopic from '../components/forum/topic.vue';
import VuePagination from '../components/pagination.vue';
import PerfectScrollbar from '../components/perfect-scrollbar';
import {VueTimeAgo} from '../plugins/timeago';
import store from "../store";
import {Hit, Hits, SearchOptions, Sort} from "../types/hit";
import {Model, User} from "../types/models";
import {Models as ModelsDict} from "../types/search";
import {createVueApp, setAxiosErrorVueNotification} from "../vue";

interface ForumItem {
  id: number;
  name: string;
  indent: boolean;
}

enum SortOptions {
  'score' = 'Trafność',
  'date' = 'Data'
}

declare global {
  interface Window {
    hits: Hits;
    model?: Model;
    query: string;
    sort: Sort;
    page: number;
    postsPerPage: number;
    pageLimit: number;
    categories: number[];
    forums: ForumItem[];
    user: string;
  }
}

setAxiosErrorVueNotification();

const VueResultCommon = {
  components: {
    'vue-timeago': VueTimeAgo,
  },
  props: {
    hits: {type: Array},
  },
  methods: {
    title(hit: Hit) {
      if (hit.title) {
        return hit.title;
      }
      if (hit.name) {
        return hit.name;
      }
      return hit.text;
    },
  },
  template: `
    <ul id="search-results" class="list-unstyled">
      <li v-for="hit in hits">
        <h2 class="mt-4 mb-2 text-truncate">
          <a :href="hit.url" v-html="title(hit)"/>
        </h2>
        <div class="mb-2">
          <span class="text-muted">
            <vue-timeago :datetime="hit.created_at"/>
          </span>
          <span v-html="hit.text"/>
        </div>
        <ul v-if="hit.children.length" class="children mt-2 mb-2">
          <li v-for="child in hit.children">
            <a :href="child.url" class="text-truncate" v-html="child.text"></a>
            <vue-timeago :datetime="child.created_at" class="text-muted"/>
          </li>
        </ul>
        <ul class="breadcrumb d-inline-flex p-0">
          <li v-for="breadcrumb in hit.breadcrumbs" class="breadcrumb-item">
            <a :href="breadcrumb.url">{{ breadcrumb.name }}</a>
          </li>
        </ul>
      </li>
    </ul>`,
};

const VueResultTopic = {
  props: {
    hits: {type: Array},
  },
  components: {'vue-topic': VueTopic},
  render() {
    const createElement = h;
    let items: VNode[] = [];
    this.hits.forEach(hit => items.push(createElement(VueTopic, {topic: hit, postsPerPage: window.postsPerPage, showCategoryName: true})));
    return createElement('div', {class: 'card card-default card-topics'}, items);
  },
};

createVueApp('Search', '#js-search', {
  delimiters: ['${', '}'],
  data: () => ({
    hits: window.hits,
    model: window.model,
    query: window.query,
    sort: window.sort,
    page: window.page,
    categories: window.categories,
    forums: window.forums,
    defaults: {sort: 'score'},
    sortOptions: SortOptions,
    modelOptions: ModelsDict,
    user: window.user,
    isDropdownVisible: false,
    pageLimit: window.pageLimit,
  }),
  components: {
    'vue-pagination': VuePagination,
    'perfect-scrollbar': PerfectScrollbar,
    'vue-autocomplete': VueAutocomplete,
    'vue-dropdown-menu': VueDropdownMenu,
    'vue-result-topic': VueResultTopic,
    'vue-result-common': VueResultCommon,
  },
  store,
  created() {
    store.commit('topics/init', window.hits.data || []);
  },

  mounted() {
    window.addEventListener('popstate', this.popState);
    this.pushState();
  },

  methods: {
    getComponent() {
      return this.model === Model.Topic ? `vue-result-topic` : 'vue-result-common';
    },

    setSort(sort: Sort) {
      this.sort = sort;
      this.request();
    },

    setModel(model?: Model) {
      // before making a request, we must clear results list because on different tabs, data could have different format
      this.hits.data = [];
      // reset page number
      this.page = 0;
      this.model = model;

      this.request();
    },

    resetDefaults() {
      this.hits.data = [];
      this.page = 0;
      this.model = undefined;
      this.sort = 'score';
      this.user = '';
      this.query = '';
      this.categories = [];
    },

    setUser(user: User) {
      this.user = user?.name;

      this.request();
    },

    setPage(page: number) {
      this.page = page;

      this.request();
      window.scrollTo(0, 0);
    },

    toggleCategory(id: number) {
      const index = this.categories.indexOf(id);

      index > -1 ? this.categories.splice(index, 1) : this.categories.push(id);

      this.request();
    },

    modelUrl(model?: Model) {
      let params = {...this.requestParams, model};
      delete params['page'];

      if (!model) {
        delete params['model'];
      }

      return this.getUrl(params);
    },

    sortUrl(sort: Sort) {
      return this.getUrl({...this.requestParams, sort});
    },

    request() {
      axios.get<any>(`/Search?timestamp=${new Date().getTime()}`, {params: this.requestParams}).then(result => {
        this.hits = result.data;

        this.pushState();
      });
    },

    getUrl(params: any): string {
      return `/Search?${new URLSearchParams(params).toString()}`;
    },

    pushState() {
      // history.pushState({params: this.requestParams, hits: this.hits}, '', this.getUrl(this.requestParams));
    },

    popState(event) {
      if (!event.state) {
        return;
      }

      this.resetDefaults();

      Object.keys(event.state.params).forEach(key => this[key] = event.state.params[key]);
      this.hits = event.state.hits;
    },
  },

  computed: {
    requestParams(): SearchOptions {
      let params = {q: this.query, model: this.model, page: this.page, sort: this.sort, categories: this.categories, user: this.user};

      Object.keys(params).forEach(key => {
        if (!params[key] || (Array.isArray(params[key]) && params[key].length === 0)) {
          delete params[key];
        }
      });

      if (this.page === 1) {
        // @ts-ignore
        delete params['page'];
      }

      return params;
    },

    defaultSort(): Sort {
      return this.sort || this.defaults.sort;
    },

    selectedCategories(): string {
      return this
        .categories
        .map(id => {
          const index = this.forums.findIndex(forum => forum.id == id); // == because id can be string

          // operator "?" is import. category ID passed in URL could be hidden for given user
          return this.forums[index]?.name;
        })
        .splice(0, 5)
        .join(', ');
    },

    shouldShowCategories(): boolean {
      return !this.model || this.model === Model.Topic;
    },
  },
});

(function (history) {
  const pushState = history.pushState;

  history.pushState = function (state) {
    // @ts-ignore
    if (typeof history.onpushstate == "function") {
      // @ts-ignore
      history.onpushstate({state: state});
    }

    // @ts-ignore
    return pushState.apply(history, arguments);
  };
})(window.history);
