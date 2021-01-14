<template>
  <button v-if="shouldShow" :class="{'follows': follows}" @click="toggleState" class="btn btn-follow">
    <i class="fa fa-fw fa-check"></i>

    <slot>
      {{ follows ? 'Obserwujesz' : 'Obserwuj' }}
    </slot>
  </button>
</template>

<script lang="ts">
  import Vue from 'vue';
  import Component from "vue-class-component";
  import { Prop } from 'vue-property-decorator';
  import store from '@/store';

  @Component({
    store
  })
  export default class VueFollowButton extends Vue {
    @Prop()
    userId!: number;

    toggleState() {
      this.follows ? store.dispatch('user/unfollow', this.userId) : store.dispatch('user/follow', this.userId);
    }

    get follows() {
      return store.getters['user/follows'](this.userId);
    }

    get shouldShow() {
      return store.getters['user/isAuthorized'] && store.state.user.user.id !== this.userId;
    }
  }
</script>

