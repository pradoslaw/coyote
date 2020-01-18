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
  computed: {
    sections() {
      return Object.values(
        this
        .forums
        .reduce((acc, forum) => {
          if (!acc[forum.section]) {
            acc[forum.section] = {name: forum.section, order: forum.order, categories: []};
          }

          acc[forum.section].categories.push(forum);

          return acc;
        }, {}))
        .sort((a, b) => {
          return (a.order < b.order ? -1 : 1);
        });
    }
  }
});
