import PerfectScrollbar from 'perfect-scrollbar';
import axios from 'axios';
import Vue from "vue";
import VueFollowButton from "@/components/forms/follow-button.vue";
import VueTags from "@/components/tags.vue";
import store from "@/store";
import {default as SkillsMixin} from "@/components/mixins/skills";

new Vue({
  el: '#js-profile',
  delimiters: ['${', '}'],
  components: { 'vue-follow-button': VueFollowButton, 'vue-tags': VueTags },
  mixins: [ SkillsMixin ],
  data() {
    return {
      skills: window.skills
    };
  },
  methods: {

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

    axios.get(`/Profile/${window.userId}/History`, {params: {offset: offset}}).then(data => {
      el.insertAdjacentHTML('beforeend', data.data);

      isPending = false;
    });
  });
})();
