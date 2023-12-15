import VueComment from '@/components/comments/comment.vue';
import VueFlag from '@/components/flags/flag.vue';
import VueMap from '@/components/google-maps/map.vue';
import VueMarker from '@/components/google-maps/marker.vue';
import {default as mixins} from '@/components/mixins/user';
import VueAutosize from '@/plugins/autosize';
import store from '@/store';
import Vue from 'vue';
import {mapGetters, mapState} from 'vuex';
import VueCommentForm from "../../components/comments/form.vue";

Vue.use(VueAutosize);

new Vue({
  el: '#js-flags',
  delimiters: ['${', '}'],
  data: {job: window.job},
  components: {'vue-flag': VueFlag},
  store,
  created() {
    store.commit('flags/init', window.flags);
  },
  computed: {
    flags() {
      return store.getters['flags/filter'](this.job.id, 'Coyote\\Job').filter(flag => flag.resources.length === 1);
    }
  }
});

new Vue({
  el: '#map',
  delimiters: ['${', '}'],
  components: {
    'vue-map': VueMap,
    'vue-marker': VueMarker
  }
});

new Vue({
  el: '#js-sidemenu',
  delimiters: ['${', '}'],
  data: {job: window.job},
  store,
  mixins: [mixins],
  created() {
    store.state.jobs.subscriptions = window.subscriptions;
  },
  methods: {
    subscribe() {
      store.dispatch('jobs/subscribe', this.job);
    }
  },
  computed: {
    ...mapGetters('jobs', ['isSubscribed']),
    ...mapGetters('user', ['isAuthorized'])
  }
});

new Vue({
  el: '#js-comments',
  delimiters: ['${', '}'],
  mixins: [mixins],
  components: {
    'vue-comment': VueComment,
    'vue-comment-form': VueCommentForm
  },
  store,
  created() {
    store.commit('comments/INIT', Array.isArray(window.comments) ? {} : window.comments);
  },
  computed: {
    ...mapState('comments', ['comments']),
    ...mapGetters('user', ['isAuthorized']),

    commentsCount() {
      return Object.keys(store.state.comments.comments).length;
    },
  }
});
