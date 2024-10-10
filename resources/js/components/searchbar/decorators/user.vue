<template>
  <a :href="profileUrl" class="d-flex align-items-center text-truncate" tabindex="-1">
    <vue-icon-avatar :user="item"/>
    <span v-html="highlight(item.name)"/>
    <div class="item-options">
      <a class="ms-3" :href="profileUrl" title="Przejdź do profilu użytkownika">
        <vue-icon name="autocompleteUserShowProfile"/>
      </a>
      <a class="ms-3" :href="messageUrl" title="Napisz wiadomość">
        <vue-icon name="autocompleteUserPrivateMessage"/>
      </a>
      <a class="ms-3" :href="postsUrl" title="Znajdź posty użytkownika">
        <vue-icon name="autocompleteUserFindPosts"/>
      </a>
    </div>
  </a>
</template>

<script lang="ts">
import VueIconAvatar from '../../icon-avatar.vue';
import VueIcon from "../../icon";
import DecoratorMixin from '../mixin';

export default {
  mixins: [DecoratorMixin],
  components: {
    'vue-icon-avatar': VueIconAvatar,
    'vue-icon': VueIcon,
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
