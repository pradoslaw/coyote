<template>
  <div v-if="isBlocked()" class="card card-post is-deleted">
    <div class="post-delete card-body">Treść posta została ukryta ponieważ autorem jest zablokowany przez Ciebie użytkownik.</div>
  </div>
  <vue-post v-else :post="post" @reply="reply"></vue-post>
</template>

<script lang="ts">
import VuePost from '@/components/forum/post.vue';
import Vue from 'vue';

export default Vue.extend({
  name: 'PostWrapper',
  components: {VuePost},
  props: {
    post: {
      type: Object,
      required: true,
    },
  },
  methods: {
    isBlocked() {
      return this.post.user_id && this.$store.getters['user/isBlocked'](this.post.user_id);
    },
    reply() {
      this.$emit('reply', ...arguments);
    },
  },
});
</script>
