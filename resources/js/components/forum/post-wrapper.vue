<template>
  <div v-if="isBlocked()" class="card card-post is-deleted"><div class="post-delete card-body">Treść postu została ukryta ponieważ autorem jest zablokowany przez Ciebie użytkownik.</div></div>
  <vue-post v-else :post="post" @reply="$emit('reply', post)"></vue-post>
</template>

<script lang="ts">
  import Vue from 'vue';
  import { Prop } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { Post } from '@/types/models';
  import VuePost from '@/components/forum/post.vue';

  @Component({
    components: { VuePost }
  })
  export default class PostWrapper extends Vue {
    @Prop()
    readonly post!: Post;

    isBlocked() {
      return this.post.user_id && this.$store.getters['user/isBlocked'](this.post.user_id);
    }
  }
</script>
