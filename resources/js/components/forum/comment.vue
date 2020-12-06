<template>
  <div :id="anchor" :class="{'highlight-flash': highlight}" class="post-comment">
    <template v-if="!isEditing">
      <span v-html="comment.html"></span> &mdash;

      <vue-user-name :user="comment.user" :owner="comment.user.id === topic.owner_id"></vue-user-name>

      <a :href="`#comment-${comment.id}`"><vue-timeago :datetime="comment.created_at" class="text-muted small"></vue-timeago></a>

      <a v-if="comment.editable" @click="edit" href="javascript:" title="Edytuj ten komentarz" class="btn-comment">
        <i class="fas fa-pencil-alt"></i>
      </a>

      <a v-if="comment.editable" @click="deleteComment(true)" href="javascript:" title="Usuń ten komentarz" class="btn-comment">
        <i class="fas fa-times"></i>
      </a>
    </template>

    <vue-comment-form v-if="isEditing" :comment="comment" @save="isEditing = false" @cancel="isEditing = false" ref="comment-form"></vue-comment-form>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import VueUserName from '../user-name.vue';
  import VueCommentForm from "./comment-form.vue";
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import { PostComment } from "../../types/models";
  import { mapGetters } from "vuex";

  @Component({
    name: 'comment',
    mixins: [clickaway, mixins],
    components: { 'vue-user-name': VueUserName, 'vue-comment-form': VueCommentForm },
    computed: mapGetters('topics', ['topic'])
  })
  export default class VueComment extends Vue {
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

    deleteComment() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć komentarz?',
        okLabel: 'Tak, usuń'
      })
      .then(() => this.$store.dispatch('posts/deleteComment', this.comment));
    }

    get anchor() {
      return `comment-${this.comment.id}`;
    }

    get highlight() {
      return '#' + this.anchor === window.location.hash;
    }
  }
</script>
