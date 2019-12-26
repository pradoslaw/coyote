import store from '../store';
import { mapState } from 'vuex';
import Vue from 'vue';
import PerfectScrollbar from '../components/perfect-scrollbar';
import VuePm from '../components/pm/message.vue';
import VueTextareaAutosize from 'vue-textarea-autosize';
import VuePrompt from '../components/forms/prompt.vue';
import VueToolbar from '../components/forms/toolbar.vue';
import VueButton from '../components/forms/button.vue';
import {default as ws} from '../libs/realtime.js';
import VueClipboard from '../plugins/clipboard.js';
import VueModal from '../components/modal.vue';
import VuePagination from '../components/pagination.vue';
import VueAutocomplete from '../components/forms/autocomplete.vue';
import Textarea from "../libs/textarea";
import axios from 'axios';

Vue.use(VueTextareaAutosize);
Vue.use(VueClipboard, {url: '/User/Pm/Paste'});

new Vue({
  el: '#app-pm',
  delimiters: ['${', '}'],
  components: {
    'perfect-scrollbar': PerfectScrollbar,
    'vue-pm': VuePm,
    'vue-prompt': VuePrompt,
    'vue-button': VueButton,
    'vue-modal': VueModal,
    'vue-toolbar': VueToolbar,
    'vue-pagination': VuePagination,
    'vue-autocomplete': VueAutocomplete
  },
  data() {
    return {
      recipient: window.data.recipient,
      text: '',
      isProcessing: false,
      errors: {},
      previewHtml: null,
      items: []
    };
  },
  store,
  created() {
    // fill vuex with data passed from controller to view
    store.commit('messages/init', {messages: window.data.messages, total: window.data.total, perPage: window.data.per_page, currentPage: window.data.current_page});
  },
  mounted() {
    this.listenForMessage();
    this.scrollToBottom();

    if ('scrollbar' in this.$refs) {
      this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-start', this.loadMore);
    }
  },
  methods: {
    scrollToBottom() {
      const overview = document.getElementById('overview');

      if (overview) {
        document.getElementById('wrap').scrollTop = overview.clientHeight;
      }
    },

    sendMessage() {
      this.isProcessing = true;
      const wasRecentlyCreated = this.messages.length === 0;

      store.dispatch('messages/add', {recipient: this.recipient.name, text: this.text})
        .then(() => {
          this.$nextTick(() => this.scrollToBottom());

          this.errors = {};
          this.text = null;
          this.previewHtml = null;

          if (wasRecentlyCreated) {
            store.dispatch('messages/loadMore');
          }
        })
        .catch(err => this.errors = err.response.data.errors)
        .finally(() => this.isProcessing = false);
    },

    insertToTextarea(file) {
      const textarea = new Textarea(this.$refs.textarea.$el);

      textarea.insertAtCaret('', '', '![' + file.name + '](' + file.url + ')');
      this.updateModel(textarea.textarea.value);
    },

    onChange(e) {
      this.updateModel(e.target.value);
    },

    showError() {
      this.$refs.error.open();
    },

    listenForMessage() {
      ws.on('Coyote\\Events\\PmCreated', data => {
        if (data.user.id === this.recipient.id) {
          store.commit('messages/add', data);

          this.$nextTick(() => this.scrollToBottom());
        }
      });
    },

    showPreview() {
      axios.post('/User/Pm/Preview', {text: this.text}).then((response) => {
        this.previewHtml = response.data;

        Prism.highlightAll();
      });
    },

    loadMore() {
      store.dispatch('messages/loadMore', this.recipient.id).then(response => {
        if (!response.data.data.length) {
          this.$refs.scrollbar.$refs.container.removeEventListener('ps-y-reach-start', this.loadMore);
        }
      });
    },

    updateModel(value) {
      this.text = value;
    },

    changePage(page) {
      store.dispatch('messages/paginate', page);
    },

    lookupName(name) {
      if (name.trim() === '') {
        return;
      }

      axios.get('/User/Prompt', {params: {q: name}}).then(response => {
        this.items = response.data.data;

        if (this.items.length === 1 && this.items[0].name.toLowerCase() === name.toLowerCase()) {
          this.items = [];
        }
      });
    },

    selectName(item) {
      this.$set(this.recipient, 'name', item.name);
    },

    markAllAsRead() {
      this.messages
        .filter(message => !message.is_read)
        .forEach(message => {
          store.commit('messages/mark', message);
          store.commit('inbox/decrement');
      });
    }
  },
  watch: {
    'recipient.name' (value) {
      this.lookupName(value);
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.total / this.perPage);
    },

    ...mapState('messages', ['messages', 'currentPage', 'total', 'perPage'])
  }
});
