import store from '../store';
import { mapState } from 'vuex';
import Vue from 'vue';
import PerfectScrollbar from '../components/perfect-scrollbar';
import VuePm from '../components/pm/message.vue';
import VueAutosize from '../plugins/autosize';
import VueAutosave from '../plugins/autosave';
import VuePrompt from '../components/forms/prompt.vue';
import VueToolbar from '../components/forms/toolbar.vue';
import VueButton from '../components/forms/button.vue';
import VueError from '../components/forms/error.vue';
import {default as ws} from '../libs/realtime.js';
import VuePaste from '../plugins/paste.js';
import VueModal from '../components/modal.vue';
import VuePagination from '../components/pagination.vue';
import VueAutocomplete from '../components/forms/autocomplete.vue';
import Textarea from "../libs/textarea";
import axios from 'axios';
import differenceInMinutes from 'date-fns/differenceInMinutes';
import parseISO from 'date-fns/parseISO';

Vue.use(VueAutosize);
Vue.use(VueAutosave, {identifier: 'pm'});
Vue.use(VuePaste, {url: '/User/Pm/Paste'});

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
    'vue-autocomplete': VueAutocomplete,
    'vue-error': VueError
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

    this.text = this.$loadDraft();
    this.$watch('text', newValue => this.$saveDraft(newValue));
  },
  mounted() {
    this.listenForMessage();
    this.listenForTyping();
    this.scrollToBottom();
    this.addScrollbarEvent();
    this.markAllAsRead();
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

          this.$removeDraft();
        })
        .catch(err => this.errors = err.response.data.errors)
        .finally(() => this.isProcessing = false);
    },

    typing() {
      ws.whisper(`user:${this.recipient.id}`, 'pm-typing', {recipient: this.sender});
    },

    insertToTextarea(file) {
      const textarea = new Textarea(this.$refs.textarea);

      textarea.insertAtCaret('', '', '![' + file.name + '](' + file.url + ')');

      this.$refs.textarea.dispatchEvent(new Event('input', {'bubbles': true}));
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
            this.markAllAsRead();
          });
        }
      });

      ws.on('Coyote\\Events\\PmRead', data => {
        const message = this.messages.find(item => item.text_id === data.text_id);

        if (message) {
          store.commit('messages/mark', message);
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
        const wrap = document.getElementById('wrap');

        if (wrap) {
          wrap.scrollTop = 1;
        }

        return response;
      });
    },

    changePage(page) {
      store.dispatch('messages/paginate', page);
    },

    selectName(item) {
      if (!item) {
        return;
      }
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
      const shouldMarkAsRead = () => Object.keys(this.recipient).length > 0;

      const listener = () => {
        this.messages
          .filter(message => message.read_at === null && message.folder === INBOX && shouldMarkAsRead())
          .forEach(message => {
            store.dispatch('messages/mark', message);
            store.commit('inbox/decrement');
          });
      };

      document.getElementById('app-pm').addEventListener('mouseover', listener, {once: true});
      document.getElementById('app-pm').addEventListener('touchmove', listener, {once: true});
    }
  },
  computed: {
    totalPages() {
      return Math.ceil(this.total / this.perPage);
    },

    sequentialMessages() {
      const isSequentialMessage = (prev, current) => {
        return prev.user.id === current.user.id && differenceInMinutes(parseISO(current.created_at), parseISO(prev.created_at)) <= 5;
      };

      return this
        .messages
        .map((item, index, array) => {
          if (index > 0) {
            item.sequential = isSequentialMessage(array[index - 1], item);
          }

          return item;
        });
    },

    ...mapState('messages', ['messages', 'currentPage', 'total', 'perPage'])
  }
});
