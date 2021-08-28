import store from '../store';
import { mapState } from 'vuex';
import Vue from 'vue';
import PerfectScrollbar from '../components/perfect-scrollbar';
import VuePm from '../components/pm/message.vue';
import VueAutosave from '../plugins/autosave';
import VuePrompt from '../components/forms/prompt.vue';
import VueMarkdown from '../components/forms/markdown.vue';
import VueButton from '../components/forms/button.vue';
import VueError from '../components/forms/error.vue';
import {default as ws} from '../libs/realtime.ts';
import VuePaste from '../plugins/paste.js';
import VuePagination from '../components/pagination.vue';
import VueAutocomplete from '../components/forms/autocomplete.vue';
import differenceInMinutes from 'date-fns/differenceInMinutes';
import parseISO from 'date-fns/parseISO';
import {default as axiosErrorHandler} from "@/libs/axios-error-handler";
import VueNotifications from "vue-notification";
import VueModals from '@/plugins/modals';

Vue.use(VueAutosave);
Vue.use(VueNotifications, {componentName: 'vue-notifications'});
Vue.use(VuePaste, {url: '/assets'});
Vue.use(VueModals);

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

const DRAFT_KEY = 'pm';
const INBOX = 1;

new Vue({
  el: '#app-pm',
  delimiters: ['${', '}'],
  components: {
    'perfect-scrollbar': PerfectScrollbar,
    'vue-pm': VuePm,
    'vue-prompt': VuePrompt,
    'vue-button': VueButton,
    'vue-markdown': VueMarkdown,
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
      items: [],
      assets: [],
      tab: 'body',
      isTyping: false
    };
  },
  store,
  created() {
    // fill vuex with data passed from controller to view
    store.commit('messages/init', {messages: window.data.messages, total: window.data.total, perPage: window.data.per_page, currentPage: window.data.current_page});

    this.text = this.$loadDraft(DRAFT_KEY);
    this.$watch('text', newValue => this.$saveDraft(DRAFT_KEY, newValue));
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

      store.dispatch('messages/add', {recipient: this.recipient.name, text: this.text, assets: this.assets})
        .then(() => {
          this.errors = {};
          this.assets = [];
          this.text = null;

          this.$nextTick(() => this.scrollToBottom());
          this.tab = 'body';

          this.$removeDraft(DRAFT_KEY);
        })
        .catch(err => this.errors = err.response.data.errors)
        .finally(() => this.isProcessing = false);
    },

    typing() {
      ws.subscribe(this.privateChannel).whisper('pm-typing', {recipient: this.sender});
    },

    listenForMessage() {
      this.channel().on('PmCreated', ({ data }) => {
        if (data.user.id === this.recipient.id) {
          store.commit('messages/add', data);

          this.$nextTick(() => {
            this.scrollToBottom();
            this.markAllAsRead();
          });
        }
      });

      // message was read by recipient
      this.channel().on('PmRead', data => {
        const message = this.messages.find(item => item.text_id === data.text_id);

        if (message) {
          store.commit('messages/mark', message);
        }
      });
    },

    listenForTyping() {
      if (!('editor' in this.$refs)) {
        return;
      }

      this.timer = null;

      ws.subscribe(this.privateChannel).on('pm-typing', data => {
        if (this.recipient.id !== data.recipient.id) {
          return;
        }

        this.isTyping = true;

        clearTimeout(this.timer);

        this.timer = setTimeout(() => this.isTyping = false, 1000);
      });

      this.$refs.editor.$refs.input.addEventListener('keyup', this.typing);
    },

    loadMore() {
      this.removeScrollbarEvent();

      return store.dispatch('messages/loadMore', this.recipient.id).then(response => {
        if (response.data.data.length) {
          this.addScrollbarEvent();
        }

        // scroll area by 1px so we can scroll to top and load more messages
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
      const listener = () => {
        const lastMessage = this.unreadMessages[this.unreadMessages.length - 1];

        if (lastMessage) {
          store.dispatch('messages/mark', lastMessage);

          this.unreadMessages.forEach(message => store.commit('messages/mark', message));
        }
      };

      document.getElementById('app-pm').addEventListener('mouseover', listener, {once: true});
      document.getElementById('app-pm').addEventListener('touchmove', listener, {once: true});
    },

    channel() {
      return ws.subscribe(`user:${store.state.user.user.id}`);
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

    unreadMessages() {
      const shouldMarkAsRead = () => Object.keys(this.recipient).length > 0;

      return this.messages
        .filter(message => message.read_at === null && message.folder === INBOX && shouldMarkAsRead());
    },

    privateChannel() {
      return 'private:' + [store.state.user.user.id, this.recipient.id].sort((a, b) => a - b).join('');
    },

    ...mapState('messages', ['messages', 'currentPage', 'total', 'perPage'])
  }
});
