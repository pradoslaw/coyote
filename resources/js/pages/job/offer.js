import Vue from 'vue';
import VueComment from '../../components/job/comment.vue';
import VueModal from '../../components/modal.vue';
import VueAutosize from '../../plugins/autosize';
import VuePrompt from '../../components/forms/prompt.vue';
import VueButton from '../../components/forms/button.vue';
import axios from 'axios';
import store from '../../store';
import VueMap from '../../components/google-maps/map.vue';
import VueMarker from '../../components/google-maps/marker.vue';
import VueNotifications from "vue-notification";
import VueFlag from '../../components/flags/flag.vue';
import { default as axiosErrorHandler } from '../../libs/axios-error-handler';
import { mapState } from 'vuex';

Vue.use(VueAutosize);
Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  el: '#comments',
  delimiters: ['${', '}'],
  components: {
    'vue-comment': VueComment,
    'vue-modal': VueModal,
    'vue-prompt': VuePrompt,
    'vue-button': VueButton
  },
  data: {
    comment: {
      text: '',
      email: ''
    },
    textFocused: false,
    isSubmitting: false
  },
  store,
  created() {
    // fill vuex with data passed from controller to view
    store.commit('jobs/SET_COMMENTS', window.comments);
  },
  methods: {
    saveComment() {
      this.isSubmitting = true;

      store
        .dispatch('jobs/saveComment', Object.assign(this.comment, {'job_id': window.job.id}))
        .then(() => this.comment = { text: '', email: '' })
        .finally(() => this.isSubmitting = false);
    }
  },
  computed: mapState('jobs', ['comments'])
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
      return store.getters['flags/filter'](this.job.id);
    }
  }
});
