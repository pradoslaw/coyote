<template>
  <button v-if="shouldShow" :class="{'follows': follows}" @click="checkAuth(toggleState)" class="btn btn-follow">
    <i class="fa fa-fw fa-check"></i>

    <slot>
      {{ follows ? 'Obserwujesz' : 'Obserwuj' }}
    </slot>
  </button>
</template>

<script lang="ts">
import Vue from 'vue';
import { mapGetters } from 'vuex';
import { default as mixin } from '@/components/mixins/user';
import store from '@/store';

export default Vue.extend({
  name: 'VueFollowButton',
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
      this.follows ? store.dispatch('user/unfollow', this.userId) : store.dispatch('user/follow', this.userId);
    },
  },
});
</script>

