<template>
  <div class="post-comment">
    <span v-html="comment.html"></span> -

    <vue-user-name :user="comment.user"></vue-user-name>

    <vue-timeago :datetime="comment.created_at" class="text-muted small"></vue-timeago>

    <a href="javascript:" title="Edytuj ten komentarz" class="btn-comment">
      <i class="fas fa-pencil-alt"></i>
    </a>

    <a href="javascript:" title="Usuń ten komentarz" class="btn-comment">
      <i class="fas fa-times"></i>
    </a>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import VueUserName from '../user-name.vue';
  import VueModal from '../modal.vue';
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref, Mixins } from "vue-property-decorator";
  import {mapActions, mapGetters} from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import { PostComment } from "../../types/models";


  @Component({
    name: 'comment',
    mixins: [clickaway, mixins],
    store,
    components: { 'vue-modal': VueModal, 'vue-user-name': VueUserName },
  })
  export default class VueComment extends Vue {

    @Ref()
    readonly confirm!: VueModal;

    @Prop(Object)
    comment!: PostComment;
  }
</script>
