import 'jquery-prettytextdiff/jquery.pretty-text-diff';
import '../pages/forum/sidebar';
import VueTimeago from '../plugins/timeago';
import VueSection from '../components/forum/section.vue';
import VueTopic from '../components/forum/topic.vue';
import VuePost from '../components/forum/post.vue';
import VueForm from '../components/forum/form.vue';
import VuePoll from '../components/forum/poll.vue';
import VueModal from '../components/modal.vue';
import VueButton from '../components/forms/button.vue';
import VueSelect from '../components/forms/select.vue';
import VueNotifications from 'vue-notification';
import Vue from "vue";
import store from '../store';
import { default as mixin } from '../components/mixins/user';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';
import { mapState, mapGetters } from "vuex";
import axios from 'axios';

Vue.use(VueTimeago);
Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

function pluck(arr, field) {
  return arr.reduce((acc, curr) => {
    acc.push(curr[field]);

    return acc;
  }, []);
}

let ForumVue = Vue.extend({
  delimiters: ['${', '}'],
  store,
  data: () => ({
    collapse: window.collapse || {},
    postsPerPage: window.postsPerPage || null,
    flags: window.flags || [],
    showCategoryName: window.showCategoryName || false,
    groupStickyTopics: window.groupStickyTopics || false,
    tags: window.tags || {}
  }),
  components: {
    'vue-section': VueSection,
    'vue-topic': VueTopic
  },
  created() {
    store.commit('forums/init', window.forums || []);
    store.commit('topics/init', (window.topics?.data) || []);
  },
  methods: {
    changeCollapse(id) {
      this.$set(this.collapse, id, !(!!(this.collapse[id])));
    },

    getFlag(topicId) {
      return this.flags[topicId];
    },

    containsUserTags(topic) {
      if (!topic.tags) {
        return false;
      }

      return topic.tags.filter(tag => this.tagNames.includes(tag.name)).length > 0;
    }
  },
  computed: {
    forums() {
      return store.state.forums.categories;
    },

    sections() {
      return Object.values(
        this
          .forums
          .sort((a, b) => a.order < b.order ? -1 : 1)
          .reduce((acc, forum) => {
            if (!acc[forum.section]) {
              acc[forum.section] = {name: forum.section, order: forum.order, categories: [], isCollapse: !!(this.collapse[forum.id])};
            }

            acc[forum.section].categories.push(forum);

            return acc;
          }, {})
        ).sort((a, b) => a.order < b.order ? -1 : 1); // sort sections
    },

    groups() {
      return this.topics.reduce((acc, item) => {
        let index = this.groupStickyTopics ? (+!item.is_sticky) : 0;

        if (!acc[index]) {
          acc[index] = [];
        }

        acc[index].push(item);

        return acc;
      }, {});
    },

    tagNames() {
      return pluck(this.tags, 'name');
    },

    ...mapState('topics', ['topics'])
  }
});

new Vue({
  el: '#js-sidebar',
  delimiters: ['${', '}'],
  mixins: [ mixin ],
  components: { 'vue-modal': VueModal, 'vue-button': VueButton, 'vue-select': VueSelect },
  store,
  data() {
    return {
      topic: window.topic,
      forum: window.forum,
      allForums: window.allForums,
      reasons: window.reasons,
      isProcessing: false,
      forumId: null,
      reasonId: null
    }
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

      store.dispatch('topics/move', { topic: this.topic, forumId: this.forumId, reasonId: this.reasonId })
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    },

    changeTitle() {
      this.isProcessing = true;

      store.dispatch('topics/changeTitle', { topic: this.topic })
        .then(result => window.location.href = result.data.url)
        .finally(() => this.isProcessing = false);
    }
  }
});

new Vue({
  el: '#js-tags',
  delimiters: ['${', '}'],
  components: { 'vue-button': VueButton },
  data: () => ({
    tags: window.tags,
    isProcessing: false,
    isEditing: false
  }),
  methods: {
    saveTags() {
      this.isProcessing = true;

      const tags = this.$refs.input.value.replace(new RegExp(',', 'g'), ' ').split(' ').filter(tag => tag !== '');

      axios.post('/Forum/Tag/Save', { tags })
        .then(result => {
          this.tags = result.data;
          this.isEditing = false;
        })
        .finally(() => this.isProcessing = false);
    },

    toggleEdit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        this.$nextTick(() => this.$refs.input.focus());
      }
    }
  },
  computed: {
    inlineTags () {
      return pluck(this.tags, 'name').join(', ');
    }
  }
})

let PostVue = Vue.extend({
  delimiters: ['${', '}'],
  components: { 'vue-post': VuePost, 'vue-form': VueForm, 'vue-poll': VuePoll },
  store,
  data: () => ({
    showStickyCheckbox: window.showStickyCheckbox,
    undefinedPost: { text: '', html: '', attachments: [] },
    reasons: window.reasons
  }),
  created() {
    store.commit('posts/init', window.pagination);
    store.commit('topics/init', [ window.topic ]);
    store.commit('forums/init', [ window.forum ]);
    store.commit('poll/init', window.poll);
  },
  mounted() {
    const hints = ['hint-subject', 'hint-text', 'hint-tags', 'hint-user_name'];

    [
      document.querySelector('#js-submit-form input[name="subject"]'),
      document.querySelector('#js-submit-form textarea[name="text"]'),
      document.querySelector('#js-submit-form input[name="tags"]')
    ].forEach(el => {
      if (!el) {
        return;
      }

      el.addEventListener('focus', () => {
        const name = el.getAttribute('name');
        const hint = document.getElementById(`hint-${name}`);

        if (!hint) {
          return; // hint tooltips might not be present on the website
        }

        hints.forEach(hint => document.getElementById(hint).style.display = 'none');
        hint.style.display = 'block';
      });
    });
  },
  methods: {
    redirectToTopic(post) {
      window.location.href = post.url;
    },

    reply(post) {
      let text = `> ##### [${post.user ? post.user.name : post.user_name} napisał(a)](/Forum/Post/${post.id}):`

      text += "\n" + post.text.replace(/\n/, "\n> ") + "\n\n"

      this.undefinedPost.text += text;

      document.getElementById('js-submit-form').scrollIntoView();

      this.$refs['js-submit-form'].$refs['textarea'].focus();
    }
  },
  computed: {
    ...mapGetters('posts', ['posts']),
    ...mapGetters('topics', ['topic']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapState('poll', ['poll'])
  }
});

const boot = { 'js-forum': ForumVue, 'js-post': PostVue }

for (let el in boot) {
  if (document.getElementById(el)) {
    new boot[el]().$mount('#' + el);
  }
}

function addEventListener(selector, event, handler) {
  let el = document.getElementById(selector);

  if (el) {
    el.addEventListener(event, handler);
  }
}

addEventListener('js-forum-list', 'change', event => window.location.href = `/Forum/${event.target.value}`);

addEventListener('js-reload', 'click', () => window.location.reload());

addEventListener('js-per-page', 'change', event => {
  const perPage = event.target.value;
  const url = event.target.dataset.url;

  window.location.href = `${url}?perPage=${perPage}`;
});
