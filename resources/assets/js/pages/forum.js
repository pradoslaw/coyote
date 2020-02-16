import 'jquery-color-animation/jquery.animate-colors';
import 'jquery-prettytextdiff/jquery.pretty-text-diff';
import '../plugins/tags';
import '../pages/forum/draft';
import '../pages/forum/posting';
import '../pages/forum/sidebar';
import '../pages/forum/tags';
// import 'bootstrap/js/src/popover';
import VueSection from '../components/forum/section.vue';
import Vue from "vue";
import store from '../store';

new Vue({
  el: '#js-forum',
  delimiters: ['${', '}'],
  store,
  data: { collapse: 'collapse' in window ? collapse : {} },
  components: {
    'vue-section': VueSection
  },
  created() {
    store.commit('forums/init', window.forums);
  },
  methods: {
    changeCollapse(id) {
      this.$set(this.collapse, id, !(!!(this.collapse[id])));
    }
  },
  computed: {
    forums() {
      return store.state.forums.categories;
    },

    sections() {
      return Object.values(
        this
          .forums
          .sort((a, b) => a.order < b.order ? -1 : 1)
          .reduce((acc, forum) => {
            if (!acc[forum.section]) {
              acc[forum.section] = {name: forum.section, order: forum.order, categories: [], isCollapse: !!(this.collapse[forum.id])};
            }

            acc[forum.section].categories.push(forum);

            return acc;
          }, {})
        ).sort((a, b) => a.order < b.order ? -1 : 1); // sort sections
    }
  }
});

new Vue({
  el: '#js-sidebar',
  delimiters: ['${', '}'],
  store,
  methods: {
    markAll() {
      store.dispatch('forums/markAll');
    }
  }
});
