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

new Vue({
  el: '#page-forum',
  delimiters: ['${', '}'],
  data: window.data,
  components: {
    'vue-section': VueSection
  },
  mounted() {
  },
  methods: {
    setOrder({id, order}) {
      const index = this.forums.findIndex(value => value.id === id);

      this.$set(this.forums, index, Object.assign(this.forums[index], {order}));

      // console.log(this.forums);
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
