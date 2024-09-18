import axios from 'axios';
import Vue from "vue";
import VueNotifications from 'vue-notification';
import {mapActions, mapGetters, mapState} from 'vuex';

import VueAvatar from '../components/avatar.vue';
import VueFollowButton from '../components/forms/follow-button.vue';
import VueTagsInline from '../components/forms/tags-inline.vue';
import {default as SkillsMixin} from '../components/mixins/skills.js';
import VueModal from '../components/modal.vue';
import VueTags from '../components/tags.vue';
import VueThumbnail from '../components/thumbnail.vue';
import VueUserName from '../components/user-name.vue';
import {default as axiosErrorHandler} from '../libs/axios-error-handler.js';
import {VueTimeAgo} from '../plugins/timeago.js';
import store from '../store/index';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  name: 'User',
  el: '#js-user',
  delimiters: ['${', '}'],
  components: {'vue-thumbnail': VueThumbnail},
  methods: {
    setPhoto(data) {
      store.commit('user/update', {photo: data.url});
    },

    deletePhoto() {
      axios.delete('/User/Photo/Delete');

      store.commit('user/update', {photo: null});
    },
  },
  computed: {
    url() {
      return store.state.user.user.photo;
    },
  },
});

new Vue({
  name: 'Skills',
  el: '#js-skills',
  delimiters: ['${', '}'],
  mixins: [SkillsMixin],
  components: {
    'vue-tags': VueTags,
    'vue-tags-inline': VueTagsInline,
  },
  data() {
    return {
      skills: window.skills,
      rateLabels: window.rateLabels,
    };
  },
  methods: {
    addSkill(tag) {
      const defaultRate = 2;

      axios.post('/User/Skills', {name: tag.name, priority: defaultRate}).then(response => {
        this.skills.push(Object.assign(response.data, {priority: defaultRate}));
      });
    },

    updateSkill(tag) {
      axios.post(`/User/Skills/${tag.id}`, tag);
    },

    deleteSkill(tag) {
      this.skills.splice(this.skills.findIndex(skill => skill.id === tag.id), 1);

      axios.delete(`/User/Skills/${tag.id}`);
    },
  },
});

new Vue({
  name: 'Followers',
  el: '#js-followers',
  delimiters: ['${', '}'],
  store,
  components: {'vue-avatar': VueAvatar, 'vue-username': VueUserName, 'vue-follow-button': VueFollowButton},
  data() {
    return {
      users: window.users,
    };
  },
  methods: {
    user(userId) {
      return this.users.find(user => user.id === userId);
    },

    ...mapActions('user', ['unfollow']),
  },
  computed: {
    ...mapState('user', ['followers']),
    ...mapGetters('user', ['isBlocked']),
  },
});

new Vue({
  name: 'Tokens',
  el: '#js-tokens',
  delimiters: ['${', '}'],
  components: {
    'vue-modal': VueModal,
    'vue-timeago': VueTimeAgo,
  },
  data() {
    return {
      tokens: [],
      tokenName: null,
      tokenId: null,
    };
  },
  mounted() {
    this.loadTokens();
  },
  methods: {
    loadTokens() {
      axios.get('/oauth/personal-access-tokens').then(response => this.tokens = response.data);
    },

    addToken() {
      axios.post('/oauth/personal-access-tokens', {name: this.tokenName})
        .then(response => {
          this.tokenId = response.data.accessToken;
          this.loadTokens();

          this.$refs.modal.open();
          this.tokenName = null;
        });
    },

    deleteToken(tokenId) {
      axios.delete(`/oauth/personal-access-tokens/${tokenId}`);

      this.loadTokens();
    },
  },
});
