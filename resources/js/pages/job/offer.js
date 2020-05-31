import Vue from 'vue';
import Config from '../../libs/config';
import VueComment from '../../components/job/comment.vue';
import VueModal from '../../components/modal.vue';
import VueAutosize from '../../plugins/autosize';
import VuePrompt from '../../components/forms/prompt.vue';
import axios from 'axios';
import store from '../../store';
import VueMap from '../../components/google-maps/map.vue';
import VueMarker from '../../components/google-maps/marker.vue';

Vue.use(VueAutosize);

new Vue({
  el: '#comments',
  delimiters: ['${', '}'],
  components: {
    'vue-comment': VueComment,
    'vue-modal': VueModal,
    'vue-prompt': VuePrompt
  },
  data: {
    defaultText: '',
    defaultEmail: '',
    error: '',
    textFocused: false
  },
  store,
  created: function () {
    // fill vuex with data passed from controller to view
    store.commit('comments/init', window.data.comments);
  },
  methods: {
    submitForm() {
      axios.post(this.$refs.submitForm.action, new FormData(this.$refs.submitForm))
        .then(response => {
          store.commit('comments/add', response.data);
          this.defaultText = this.defaultEmail = '';
        })
        .catch(error => {
          let errors = error.response.data.errors;

          this.error = errors[Object.keys(errors)[0]][0];
          this.$refs.error.open();
        });
    }
  },
  computed: {
    comments() {
      return store.state.comments.comments;
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
