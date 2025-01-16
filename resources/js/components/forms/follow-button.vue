<template>
  <button
    v-if="shouldShow"
    class="btn btn-follow neon-secondary-button"
    :class="{follows, 'neon-follows':follows}"
    @click="checkAuth(toggleState)"
  >
    <vue-icon name="userFollow"/>
    <slot>
      {{ follows ? 'Obserwujesz' : 'Obserwuj' }}
    </slot>
  </button>
</template>

<script lang="ts">
import {mapGetters} from 'vuex';

import store from '../../store/index';
import VueIcon from "../icon";
import {default as mixin} from '../mixins/user.js';

export default {
  name: 'VueFollowButton',
  components: {VueIcon},
  mixins: [mixin],
  props: {
    userId: {
      type: Number,
      required: true,
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    follows() {
      return store.getters['user/follows'](this.userId);
    },
    shouldShow() {
      return store.getters['user/isAuthorized'] ? store.state.user.user.id !== this.userId : true;
    },
  },
  methods: {
    toggleState() {
      if (this.follows) {
        store.dispatch('user/unfollow', this.userId);
      } else {
        store.dispatch('user/follow', this.userId);
      }
    },
  },
};
</script>

