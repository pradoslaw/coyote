<template>
  <vue-post :post="post" @reply="reply"/>
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
    reply() {
      this.$emit('reply', ...arguments);
    },
  },
});
</script>
