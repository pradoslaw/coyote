import store from '../store';
import Vue from 'vue';
import PerfectScrollbar from '../components/perfect-scrollbar';
import VuePm from '../components/pm/message.vue';
import VueTextareaAutosize from 'vue-textarea-autosize';
import VuePrompt from '../components/prompt.vue';
import VueButton from '../components/forms/button.vue';
import {default as ws} from '../libs/realtime.js';
import VueClipboard from '../plugins/clipboard.js';
import VueModal from '../components/modal.vue';
import Textarea from "../libs/textarea";
import axios from 'axios';

Vue.use(VueTextareaAutosize);
Vue.use(VueClipboard, {url: '/User/Pm/Paste'});

new Vue({
  el: '#app',
  delimiters: ['${', '}'],
  components: {
    'perfect-scrollbar': PerfectScrollbar,
    'vue-pm': VuePm,
    'vue-prompt': VuePrompt,
    'vue-button': VueButton,
    'vue-modal': VueModal
  },
  data() {
    return {
      recipient: window.data.recipient,
      text: '',
      isProcessing: false,
      errorMessage: null,
      previewHtml: null
    };
  },
  store,
  created() {
    // fill vuex with data passed from controller to view
    store.commit('messages/init', window.data.messages);
  },
  mounted() {
    this.listenForMessage();
    this.scrollToBottom();

    this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-start', this.loadMore);
  },
  methods: {
    scrollToBottom() {
      const overview = document.getElementById('overview');

      document.getElementById('wrap').scrollTop = overview.clientHeight;
    },

    sendMessage() {
      this.isProcessing = true;

      store.dispatch('messages/add', {recipient: this.recipient.name, text: this.text})
        .then(() => {
          this.$nextTick(() => {
            this.scrollToBottom();
          });

          this.error = null;
          this.text = null;
        })
        .catch(err => {
          let errors = err.response.data.errors;

          this.errorMessage = errors[Object.keys(errors)[0]][0];
        })
        .finally(() => {
          this.isProcessing = false;
        });
    },

    insertToTextarea(file) {
      const textarea = new Textarea(this.$refs.textarea.$el);

      textarea.insertAtCaret('', '', '![' + file.name + '](' + file.url + ')');
      this.text = textarea.textarea.value;
    },

    showError() {
      this.$refs.error.open();
    },

    listenForMessage() {
      ws.on('Coyote\\Events\\PmCreated', data => {
        if (data.user.id === this.recipient.id) {
          store.commit('messages/add', data);

          this.$nextTick(() => {
            this.scrollToBottom();
          });
        }
      });
    },

    showPreview() {
      axios.post('/User/Pm/Preview', {text: this.text}).then((response) => {
        this.previewHtml = response.data;
      });
    },

    loadMore() {
      store.dispatch('messages/loadMore', this.recipient.id).then(response => {
        if (!response.data.data.length) {
          this.$refs.scrollbar.$refs.container.removeEventListener('ps-y-reach-start', this.loadMore);
        }
      });
    }
  },
  computed: {
    messages() {
      return store.state.messages.messages;
    }
  }
});
