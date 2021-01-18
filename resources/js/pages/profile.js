import PerfectScrollbar from 'perfect-scrollbar';
import axios from 'axios';
import Vue from "vue";
import VueFollowButton from "@/components/forms/follow-button.vue";
import VueTags from "@/components/tags.vue";
import {default as SkillsMixin} from "@/components/mixins/skills";
import store from "@/store";
import VueNotifications from "vue-notification";
import VueModals from "@/plugins/modals";

Vue.use(VueNotifications, {componentName: 'vue-notifications'});
Vue.use(VueModals);

new Vue({
  el: '#js-profile',
  delimiters: ['${', '}'],
  components: { 'vue-follow-button': VueFollowButton, 'vue-tags': VueTags },
  mixins: [ SkillsMixin ],
  data() {
    return {
      user: window.user,
      skills: window.skills
    };
  },
  methods: {
    block() {
      this.$confirm({
        message: 'Nie będziesz widział komentarzy ani wpisów tego użytkownika',
        title: 'Zablokować użytkownika?',
        okLabel: 'Tak, zablokuj'
      })
      .then(() => {
        store.dispatch('user/block', this.user.id);

        this.$notify({type: 'success', duration: 5000, title: 'Gotowe!', text: 'Użytkownik został zablokowany.'});
      });
    }
  },
  computed: {
    isAuthorized() {
      return store.getters['user/isAuthorized'] && store.state.user.user.id !== this.user.id;
    }
  }
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
