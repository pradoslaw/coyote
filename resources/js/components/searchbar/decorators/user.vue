<template>
  <a :href="profileUrl" class="d-flex align-items-center text-truncate" tabindex="-1">
    <vue-icon-avatar :user="item"/>
    <span v-html="highlight(item.name)"/>
    <div class="item-options">
      <a class="ms-3" :href="profileUrl" title="Przejdź do profilu użytkownika">
        <i class="fas fa-user"/>
      </a>
      <a class="ms-3" :href="messageUrl" title="Napisz wiadomość">
        <i class="fas fa-comment"/>
      </a>
      <a class="ms-3" :href="postsUrl" title="Znajdź posty użytkownika">
        <i class="fas fa-magnifying-glass"/>
      </a>
    </div>
  </a>
</template>

<script lang="ts">
import VueIconAvatar from '../../icon-avatar.vue';
import DecoratorMixin from '../mixin';

export default {
  mixins: [DecoratorMixin],
  components: {
    'vue-icon-avatar': VueIconAvatar,
  },
  computed: {
    profileUrl() {
      return this.$props.item.url;
    },
    messageUrl() {
      return '/User/Pm/Submit?to=' + this.$props.item.name;
    },
    postsUrl() {
      return '/Search?model=Topic&sort=date&user=' + this.$props.item.name;
    },
  },
};
</script>
