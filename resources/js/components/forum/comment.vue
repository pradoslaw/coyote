<template>
  <div :id="`comment-${comment.id}`" class="post-comment">
    <template v-if="!isEditing">
      <span v-html="comment.html"></span>

      <vue-user-name :user="comment.user"></vue-user-name>

      <a :href="`#comment-${comment.id}`"><vue-timeago :datetime="comment.created_at" class="text-muted small"></vue-timeago></a>

      <a @click="edit" href="javascript:" title="Edytuj ten komentarz" class="btn-comment">
        <i class="fas fa-pencil-alt"></i>
      </a>

      <a href="javascript:" title="Usuń ten komentarz" class="btn-comment">
        <i class="fas fa-times"></i>
      </a>
    </template>

    <vue-comment-form v-if="isEditing" :comment="comment" @save="isEditing = false" @cancel="isEditing = false" ref="comment-form"></vue-comment-form>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import VueUserName from '../user-name.vue';
  import VueModal from '../modal.vue';
  import VueCommentForm from "./comment-form.vue";
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
    components: { 'vue-modal': VueModal, 'vue-user-name': VueUserName, 'vue-comment-form': VueCommentForm },
  })
  export default class VueComment extends Vue {
    @Ref()
    readonly confirm!: VueModal;

    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Prop(Object)
    comment!: PostComment;

    private isEditing = false;

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        this.$nextTick(() => this.commentForm.textarea.focus());
      }
    }
  }
</script>
