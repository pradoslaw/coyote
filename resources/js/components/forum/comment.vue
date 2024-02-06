<template>
  <div :id="anchor" :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false}" class="post-comment">
    <template v-if="!comment.is_editing">
      <span v-html="comment.html"></span> &mdash;

      <vue-username :user="comment.user" :owner="comment.user.id === topic.owner_id"></vue-username>

      <a :href="comment.url"><vue-timeago :datetime="comment.created_at" class="text-muted small"></vue-timeago></a>

      <a v-if="comment.editable" @click="edit" href="javascript:" title="Edytuj ten komentarz" class="btn-comment">
        <i class="fas fa-pencil-alt"></i>
      </a>

      <a v-if="comment.editable" @click="deleteComment" href="javascript:" title="Usuń ten komentarz" class="btn-comment">
        <i class="fas fa-trash-alt"></i>
      </a>

      <a v-if="comment.editable" @click="migrate" href="javascript:" title="Zamień w post" class="btn-comment">
        <i class="fas fa-compress"></i>
      </a>
    </template>

    <vue-comment-form
      v-if="comment.is_editing"
      :comment="comment"
      @save="$store.commit('posts/edit', comment)"
      @cancel="$store.commit('posts/edit', comment)"
      ref="comment-form"
    ></vue-comment-form>
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
  import { PostComment } from "@/types/models";
  import { mapGetters } from "vuex";

  @Component({
    name: 'comment',
    mixins: [clickaway, mixins],
    components: { 'vue-username': VueUserName, 'vue-comment-form': VueCommentForm },
    computed: mapGetters('topics', ['topic'])
  })
  export default class VueComment extends Vue {
    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Prop(Object)
    comment!: PostComment;

    edit() {
      this.$store.commit('posts/edit', this.comment);

      if (this.comment.is_editing) {
        this.$nextTick(() => this.commentForm.focus());
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

    migrate() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Zamienić na post?',
        okLabel: 'Tak, zamień'
      })
      .then(() => {
        this.$store.dispatch('posts/migrateComment', this.comment).then(response => window.location.href = response.data.url);
      });
    }

    get anchor() {
      return `comment-${this.comment.id}`;
    }

    get highlight() {
      return '#' + this.anchor === window.location.hash;
    }
  }
</script>
