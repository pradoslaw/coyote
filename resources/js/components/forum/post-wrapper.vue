<template>
  <vue-post :post="post" @reply="reply"/>
</template>

<script lang="ts">
import Vue from 'vue';
import VuePost from '../forum/post.vue';

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
