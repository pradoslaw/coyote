import Vue from "vue";
import VueThumbnail from '../components/thumbnail';
import VueNotifications from 'vue-notification';
import VueAvatar from '@/components/avatar';
import VueUserName from '@/components/user-name';
import VueModal from '@/components/modal';
import VueFollowButton from '@/components/forms/follow-button';
import axios from 'axios';
import store from '../store';
import { default as axiosErrorHandler } from '../libs/axios-error-handler';
import { mapActions, mapGetters, mapState } from 'vuex';

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

axiosErrorHandler(message => Vue.notify({type: 'error', text: message}));

new Vue({
  el: '#js-user',
  delimiters: ['${', '}'],
  components: { 'vue-thumbnail': VueThumbnail },
  methods: {
    setPhoto(data) {
      store.commit('user/update', { photo: data.url });
    },

    deletePhoto() {
      axios.delete('/User/Photo/Delete');

      store.commit('user/update', { photo: null });
    }
  },
  computed: {
    url() {
      return store.state.user.user.photo;
    }
  }
});

new Vue({
  el: '#js-skills',
  delimiters: ['${', '}'],
  data() {
    return {
      skills: window.skills,
      rateLabels: window.rateLabels,
      selectedMark: null,
      clickedMark: null,
      skillName: null
    };
  },
  methods: {
    addSkill() {
      axios.post('/User/Skills', {name: this.skillName, rate: this.clickedMark}).then(() => {
        this.skills.push({
          name: this.skillName,
          rate: this.clickedMark
        });

        this.clickedMark = this.selectedMark = this.skillName = null;
      });
    },

    deleteSkill(id) {
      this.skills.splice(this.skills.findIndex(skill => skill.id === id), 1);

      axios.delete(`/User/Skills/${id}`);
    },

    setMark(mark) {
      this.clickedMark = mark;
    },

    selectMark(mark) {
      this.selectedMark = mark;
    },

    clearMarks() {
      this.selectedMark = null;
    }
  }
});

new Vue({
  el: '#js-followers',
  delimiters: ['${', '}'],
  store,
  components: { 'vue-avatar': VueAvatar, 'vue-username': VueUserName, 'vue-follow-button': VueFollowButton },
  data() {
    return {
      users: window.users
    };
  },
  methods: {
    user(userId) {
      return this.users.find(user => user.id === userId);
    },

    ...mapActions('user', ['unfollow'])
  },
  computed: {
    ...mapState('user', ['followers']),
    ...mapGetters('user', ['isBlocked'])
  }
});

new Vue({
  el: '#js-tokens',
  delimiters: ['${', '}'],
  components: { 'vue-modal': VueModal },
  data() {
    return {
      tokens: [],
      tokenName: null,
      tokenId: null
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
      axios.post('/oauth/personal-access-tokens', { name: this.tokenName })
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
    }
  }
});
