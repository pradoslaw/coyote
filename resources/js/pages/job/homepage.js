import Vue from 'vue';
import VueJob from '@/components/job/job.vue';
import VueJobTiny from '@/components/job/job-tiny.vue';
import VuePagination from '@/components/pagination.vue';
import VueTabs from '@/components/tabs.vue';
import axios from 'axios';
import store from '@/store';
import PerfectScrollbar from 'perfect-scrollbar';
import { mapState } from 'vuex';
import VueNotifications from "vue-notification";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

new Vue({
  el: '#js-job',
  delimiters: ['${', '}'],
  components: {
    'vue-job': VueJob,
    'vue-pagination': VuePagination,
    'vue-job-tiny': VueJobTiny,
    'vue-notification': VueNotifications,
    'vue-tabs': VueTabs
  },
  data: window.data,
  store,
  created() {
    store.state.jobs.subscriptions = window.data.subscribed;
  },
  mounted() {
    window.history.pushState({ jobs: window.data.jobs, input: window.data.input }, '', window.location.href);

    window.onpopstate = e => {
      this.jobs = e.state?.jobs || this.jobs;
      this.input = e.state?.input || this.input;
    };

    this.initYScrollbar(document.querySelector('#js-published'));
    // this.initYScrollbar(document.querySelector('#js-subscribed'));
    //
    this.initXScrollbar(document.querySelector('#js-filter-location'));
    this.initXScrollbar(document.querySelector('#js-filter-tech'));
  },
  filters: {
    capitalize(value) {
      return value.charAt(0).toUpperCase() + value.slice(1);
    }
  },
  methods: {
    toggleTag(tag) {
      this.toggle(this.input.tags, tag);
    },

    toggleLocation(location) {
      this.toggle(this.input.locations, location);
    },

    toggle(input, item) {
      const index = input.indexOf(item);

      if (index > -1) {
        input.splice(index, 1);
      } else {
        input.push(item);
      }

      this.search();
    },

    toggleRemote() {
      if (this.input.remote) {
        this.input.remote = null;
      } else {
        this.input.remote = 1;
        this.input.remote_range = 100;
      }

      this.search();
    },

    changePage(page) {
      this.jobs.meta.current_page = page;
      this.input.page = page;

      this.search(page);

      window.scrollTo(0, 0);
    },

    search(page = null) {
      let input = {
        q: this.input.q,
        city: this.input.city,
        tags: this.input.tags,
        sort: this.input.sort,
        salary: this.input.salary,
        currency: this.input.currency,
        remote: this.input.remote,
        remote_range: this.input.remote_range,
        locations: this.input.locations
      };

      this.skeleton = true;

      if (page !== null && !isNaN(page)) {
        input = Object.assign({page}, input);
      }

      axios.get(
        `${window.location.pathname}?timestamp=${new Date().getTime()}`,
        {params: input, headers: {'Cache-Control': 'no-cache', 'Pragma': 'no-cache', 'Expires': '0'}}
      )
      .then(response => {
        this.jobs = response.data.jobs;
        this.defaults = response.data.defaults;

        window.history.pushState(response.data, '', response.data.url);
      })
      .then(() => this.skeleton = false);
    },

    includesLocation(location) {
      return this.input.locations.includes(location);
    },

    includesTag(tag) {
      return this.input.tags.includes(tag);
    },

    initYScrollbar(container) {
      if (container) {
        new PerfectScrollbar(container);
      }
    },

    initXScrollbar(container) {
      if (container) {
        const ps = new PerfectScrollbar(container, {suppressScrollY: true});

        window.addEventListener('resize', () => ps.update());
      }
    },

    isTabSelected(tab) {
      return this.selectedTab === tab;
    },

    selectTab(tab) {
      this.selectedTab = tab;
    },

    getTabDropdownClass(tab) {
      return {'fa-angle-up': this.selectedTab !== tab, 'fa-angle-down': this.selectedTab === tab};
    }
  },
  computed: {
    defaultSort: {
      get() {
        return this.input.sort ?? this.defaults.sort;
      },
      set(value) {
        this.input.sort = value;
      }
    },

    defaultCurrency: {
      get() {
        return this.input.currency ?? this.defaults.currency;
      },
      set(value) {
        this.input.currency = value;
      }
    },

    tabs() {
      return { '/Praca': 'Ogłoszenia', '/Praca/Moje': 'Moje ogłoszenia' };
    },

    ...mapState('jobs', ['subscriptions'])
  }
});
