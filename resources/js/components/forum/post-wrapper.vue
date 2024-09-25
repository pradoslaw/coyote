<template>
  <vue-post :post="post" @reply="reply"/>
</template>

<script lang="ts">
import VuePost from '../forum/post.vue';

export default {
  components: {VuePost},
  emits: ['reply'],
  props: {
    post: {
      type: Object,
      required: true,
    },
  },
  methods: {
    reply() {
      this.$emit('reply', ...arguments);
    },
  },
};
</script>
