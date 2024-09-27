<template>
  <a :href="profileUrl" class="d-flex align-items-center text-truncate" tabindex="-1">
    <vue-avatar :photo="item.photo" class="i-16 me-2"/>
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
import VueAvatar from '../../avatar.vue';
import DecoratorMixin from '../mixin';

export default {
  mixins: [DecoratorMixin],
  components: {
    'vue-avatar': VueAvatar,
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
