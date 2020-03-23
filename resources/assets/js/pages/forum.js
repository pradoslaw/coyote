import 'jquery-color-animation/jquery.animate-colors';
import 'jquery-prettytextdiff/jquery.pretty-text-diff';
import '../plugins/tags';
import '../pages/forum/draft';
import '../pages/forum/posting';
import '../pages/forum/sidebar';
import '../pages/forum/tags';
// import 'bootstrap/js/src/popover';
import VueSection from '../components/forum/section.vue';
import VueTopic from '../components/forum/topic.vue';
import Vue from "vue";
import store from '../store';
import {mapState} from "vuex";

new Vue({
  el: '#js-forum',
  delimiters: ['${', '}'],
  store,
  data: { collapse: 'collapse' in window ? window.collapse : {}, postsPerPage: window?.posts_per_page },
  components: {
    'vue-section': VueSection,
    'vue-topic': VueTopic
  },
  created() {
    store.commit('forums/init', window.forums);
    store.commit('topics/init', window.topics || []);
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
    },

    groups() {
      return this.topics.reduce((acc, item) => {
        let index = +!item.is_sticky;

        if (!acc[index]) {
          acc[index] = [];
        }

        acc[index].push(item);

        return acc;
      }, {});
    },

    ...mapState('topics', ['topics'])
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
