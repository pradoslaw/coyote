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

const INBOX = 1;

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
      sender: window.data.sender,
      text: '',
      isProcessing: false,
      errors: {},
      previewHtml: null,
      items: [],
      tab: 'body',
      isTyping: false
    };
  },
  store,
  created() {
    // fill vuex with data passed from controller to view
    store.commit('messages/init', {messages: window.data.messages, total: window.data.total, perPage: window.data.per_page, currentPage: window.data.current_page});
  },
  mounted() {
    this.listenForMessage();
    this.listenForTyping();
    this.scrollToBottom();
    this.addScrollbarEvent();
  },
  methods: {
    addScrollbarEvent() {
      if ('scrollbar' in this.$refs) {
        this.$refs.scrollbar.$refs.container.addEventListener('ps-y-reach-start', this.loadMore);
      }
    },

    removeScrollbarEvent() {
      if ('scrollbar' in this.$refs) {
        this.$refs.scrollbar.$refs.container.removeEventListener('ps-y-reach-start', this.loadMore);
      }
    },

    scrollToBottom() {
      const overview = document.getElementById('overview');

      if (overview) {
        document.getElementById('wrap').scrollTop = overview.clientHeight;
      }
    },

    sendMessage() {
      this.isProcessing = true;

      store.dispatch('messages/add', {recipient: this.recipient.name, text: this.text})
        .then(() => {
          this.errors = {};
          this.text = null;
          this.previewHtml = null;

          this.$nextTick(() => this.scrollToBottom());
          this.tab = 'body';
        })
        .catch(err => this.errors = err.response.data.errors)
        .finally(() => this.isProcessing = false);
    },

    typing() {
      ws.whisper(`user:${this.recipient.id}`, 'pm-typing', {recipient: this.sender});
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

    listenForTyping() {
      this.timer = null;

      ws.on('pm-typing', data => {
        if (this.recipient.id !== data.recipient.id) {
          return;
        }

        this.isTyping = true;

        clearTimeout(this.timer);

        this.timer = setTimeout(() => this.isTyping = false, 1000);
      });
    },

    showPreview() {
      this.tab = 'preview';

      axios.post('/User/Pm/Preview', {text: this.text}).then((response) => {
        this.previewHtml = response.data;

        Prism.highlightAll();
      });
    },

    loadMore() {
      return store.dispatch('messages/loadMore', this.recipient.id).then(response => {
        if (!response.data.data.length) {
          this.removeScrollbarEvent();
        }

        // scroll area by 1px because we don't want to run ps-y-reach-start event in circle
        document.getElementById('wrap').scrollTop = 1;

        return response;
      });
    },

    updateModel(value) {
      this.text = value;
    },

    changePage(page) {
      store.dispatch('messages/paginate', page);
    },

    lookupName(name) {
      if (!name.trim().length) {
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
      this.recipient = item;

      store.commit('messages/reset');
      this.removeScrollbarEvent();

      this.loadMore().then(() => this.$nextTick(() => {
          this.scrollToBottom();
          this.addScrollbarEvent();
        })
      );
    },

    markAllAsRead() {
      this.messages
        .filter(message => message.read_at === null && message.folder === INBOX)
        .forEach(message => {
          store.commit('messages/mark', message);
          store.commit('inbox/decrement');
      });
    }
  },
  watch: {
    'recipient.name' (newValue, oldValue) {
      if (newValue && oldValue && newValue.toLowerCase() === oldValue.toLowerCase()) {
        return;
      }

      this.lookupName(newValue);
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.total / this.perPage);
    },

    ...mapState('messages', ['messages', 'currentPage', 'total', 'perPage'])
  }
});
