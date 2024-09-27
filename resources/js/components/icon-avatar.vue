<template>
  <vue-avatar
    v-if="user.photo"
    :photo="user.photo"
    :initials="user.initials"
    class="i-16 me-2"
  />
  <div v-else class="i-16 me-2 text-center">
    <i class="fa-solid fa-user"/>
  </div>
</template>

<script>
import VueAvatar from './avatar.vue';

export default {
  components: {
    'vue-avatar': VueAvatar,
  },
  props: {
    user: {type: Object},
  },
};
</script>
