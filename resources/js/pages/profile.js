import axios from 'axios';
import PerfectScrollbar from 'perfect-scrollbar';
import Vue from "vue";
import VueNotifications from "vue-notification";
import {mapActions, mapGetters} from "vuex";

import VueFollowButton from "../components/forms/follow-button.vue";
import {default as SkillsMixin} from "../components/mixins/skills.js";
import VueTags from "../components/tags.vue";
import {confirmModal} from "../plugins/modals";
import store from "../store/index";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});

new Vue({
  name: 'Profile',
  el: '#js-profile',
  delimiters: ['${', '}'],
  components: {'vue-follow-button': VueFollowButton, 'vue-tags': VueTags},
  mixins: [SkillsMixin],
  store,
  data() {
    return {
      user: window.user,
      skills: window.skills,
    };
  },
  methods: {
    block() {
      confirmModal({
        message: 'Nie będziesz widział komentarzy ani wpisów tego użytkownika',
        title: 'Zablokować użytkownika?',
        okLabel: 'Tak, zablokuj',
      })
        .then(() => {
          store.dispatch('user/block', this.user.id);

          this.$notify({type: 'success', duration: 5000, title: 'Gotowe!', text: 'Użytkownik został zablokowany.'});
        });
    },

    ...mapActions('user', ['unblock']),
  },
  computed: {
    isAuthorized() {
      return store.getters['user/isAuthorized'] && store.state.user.user.id !== this.user.id;
    },

    ...mapGetters('user', ['isBlocked']),
  },
});

(function () {
  const container = document.getElementById('wrap');

  if (!container) {
    return;
  }

  new PerfectScrollbar(container);

  let isPending = false;

  container.addEventListener('ps-y-reach-end', function () {
    if (isPending) {
      return;
    }

    const el = document.getElementById('reputation');
    const offset = el.childNodes.length;

    isPending = true;

    axios.get(`/Profile/${window.user.id}/History`, {params: {offset: offset}}).then(data => {
      el.insertAdjacentHTML('beforeend', data.data);

      isPending = false;
    });
  });
})();
