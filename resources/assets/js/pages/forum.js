import 'jquery-color-animation/jquery.animate-colors';
import 'jquery-prettytextdiff/jquery.pretty-text-diff';
import '../plugins/tags';
import '../pages/forum/draft';
import '../pages/forum/posting';
import '../pages/forum/sidebar';
import '../pages/forum/tags';
import 'bootstrap-sass/assets/javascripts/bootstrap/popover';
import VueSection from '../components/forum/section.vue';
import Vue from "vue";
import axios from 'axios';

new Vue({
  el: '#page-forum',
  delimiters: ['${', '}'],
  data: {
    forums: window.forums
  },
  components: {
    'vue-section': VueSection
  },
  methods: {
    setup(categories) {
      categories.forEach(category => {
        const index = this.forums.findIndex(value => value.id === category.id);

        this.$set(this.forums, index, Object.assign(this.forums[index], {order: category.order}));
      });

      axios.post('/Forum/Setup', categories.map(category => this._pluck(category)));
    },

    toggle(category) {
      axios.post('/Forum/Setup', [ this._pluck(category) ]);
    },

    asRead(category) {
      axios.post(`/Forum/${category.slug}/Mark`);
    },

    _pluck(category) {
      return (({ id, is_hidden, order }) => ({ id, is_hidden, order }))(category);
    }
  },
  computed: {
    sections() {
      return Object.values(
        this
          .forums
          .sort((a, b) => a.order < b.order ? -1 : 1)
          .reduce((acc, forum) => {
            if (!acc[forum.section]) {
              acc[forum.section] = {name: forum.section, order: forum.order, categories: []};
            }

            acc[forum.section].categories.push(forum);

            return acc;
          }, {})
        ).sort((a, b) => a.order < b.order ? -1 : 1); // sort sections
    }
  }
});
