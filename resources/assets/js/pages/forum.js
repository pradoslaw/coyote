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
  data: window.data,
  components: {
    'vue-section': VueSection
  },
  mounted() {
  },
  methods: {
    saveOrder(forums) {
      forums.forEach(forum => {
        let index = this.forums.findIndex(value => value.id === forum.id);

        this.$set(this.forums, index, Object.assign(this.forums[index], {order: forum.order}));
      });

      axios.post(`/Forum/Order`, {input: forums.map(obj => {
        return  {'id': obj.id, 'order': obj.order};
      })});

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
