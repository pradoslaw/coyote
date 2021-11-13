import Vue from 'vue';
import VueComment from '@/components/comments/comment.vue';
import VueAutosize from '@/plugins/autosize';
import store from '@/store';
import VueMap from '@/components/google-maps/map.vue';
import VueMarker from '@/components/google-maps/marker.vue';
import VueFlag from '@/components/flags/flag.vue';
import { mapGetters, mapState } from 'vuex';
import { default as mixins } from '@/components/mixins/user';
import VueCommentForm from "@/components/comments/form";

Vue.use(VueAutosize);

new Vue({
  el: '#js-flags',
  delimiters: ['${', '}'],
  data: { job: window.job },
  components: { 'vue-flag': VueFlag },
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
  data: { job: window.job },
  store,
  mixins: [ mixins ],
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
  mixins: [ mixins ],
  components: {
    'vue-comment': VueComment,
    'vue-comment-form': VueCommentForm
  },
  store,
  created() {
    store.commit('comments/INIT', window.comments);
  },
  computed: {
    ...mapState('comments', ['comments']),
    ...mapGetters('user', ['isAuthorized'])
  }
});
