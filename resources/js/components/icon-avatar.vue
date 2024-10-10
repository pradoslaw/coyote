<template>
  <vue-avatar
    v-if="user.photo"
    :photo="user.photo"
    :initials="user.initials"
    class="i-16 me-2"
  />
  <div v-else class="i-16 me-2 text-center">
    <vue-icon name="autocompleteUserNoAvatar"/>
  </div>
</template>

<script>
import VueAvatar from './avatar.vue';
import VueIcon from './icon';

export default {
  components: {
    'vue-avatar': VueAvatar,
    'vue-icon': VueIcon,
  },
  props: {
    user: {type: Object},
  },
};
</script>
