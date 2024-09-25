import {mapGetters} from "vuex";

import VueButton from "../../components/forms/button.vue";
import VueSelect from "../../components/forms/select.vue";
import {default as mixin} from '../../components/mixins/user';
import VueModal from "../../components/modal.vue";
import store from "../../store";
import {Forum} from '../../types/models';

export default {
  delimiters: ['${', '}'],
  mixins: [mixin],
  components: {'vue-modal': VueModal, 'vue-button': VueButton, 'vue-select': VueSelect},
  store,
  data() {
    return {
      topic: window.topic,
      forum: window.forum,
      allForums: window.allForums,
      reasons: window.reasons,
      isProcessing: false,
      forumId: null,
      reasonId: null,
    };
  },
  methods: {
    markForums() {
      store.dispatch('forums/markAll');
      store.commit('topics/markAll');
    },

    markTopics() {
      store.dispatch('topics/markAll');
    },

    lock() {
      store.dispatch('topics/lock', this.topic);
    },

    subscribe() {
      store.dispatch('topics/subscribe', this.topic);
    },

    move() {
      this.isProcessing = true;

      store.dispatch('topics/move', {topic: this.topic, forumId: this.forumId, reasonId: this.reasonId})
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    },

    changeTitle() {
      this.isProcessing = true;

      store.dispatch('topics/changeTitle', {topic: this.topic})
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    sortedForums(): Forum[] {
      return this.allForums.map(forum => ({
        ...forum,
        name: '&nbsp;'.repeat(forum.indent * 4) + forum.name,
      }));
    },
  },
};
