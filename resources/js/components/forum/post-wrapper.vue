<template>
  <vue-post :post="post" :tree-item="treeItem" @reply="reply" :tree-topic-post-first="treeTopicPostFirst"/>
</template>

<script lang="ts">
import {Post} from "../../types/models";
import VuePost from '../forum/post.vue';

export default {
  components: {VuePost},
  emits: ['reply'],
  props: {
    post: {type: Object, required: true},
    treeItem: {type: Object, required: false},
    treeTopicPostFirst: {type: Boolean, required: false},
  },
  methods: {
    reply(post: Post, scrollIntoForm: boolean): void {
      this.$emit('reply', post, scrollIntoForm);
    },
  },
};
</script>
