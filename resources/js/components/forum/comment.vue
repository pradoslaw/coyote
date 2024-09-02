<template>
  <div :id="anchor" :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false}" class="post-comment">
    <vue-flag v-for="flag in flags" :key="flag.id" :flag.sync="flag"/>
    <template v-if="!comment.is_editing">
      <div>
        <vue-username :user="comment.user" :owner="comment.user.id === topic.owner_id"></vue-username>
        <a :href="comment.url">
          <vue-timeago :datetime="comment.created_at" class="text-muted small"></vue-timeago>
        </a>
        <a v-if="comment.editable" @click="edit" href="javascript:" title="Edytuj ten komentarz" class="btn-comment">
          <i class="fas fa-pencil"></i>
        </a>
        <a v-if="comment.editable" @click="deleteComment" href="javascript:" title="Usuń ten komentarz" class="btn-comment">
          <i class="fas fa-trash-can"></i>
        </a>
        <a v-if="comment.editable" @click="migrate" href="javascript:" title="Zamień w post" class="btn-comment">
          <i class="fas fa-compress"></i>
        </a>
        <a :data-metadata="comment.metadata" :data-url="comment.url" title="Zgłoś ten komentarz" href="javascript:" class="btn-comment">
          <i class="fas fa-flag"></i>
        </a>
      </div>
      <span v-html="comment.html" class="comment-text"/>
    </template>
    <vue-comment-form
      v-if="comment.is_editing"
      :comment="comment"
      @save="$store.commit('posts/edit', comment)"
      @cancel="$store.commit('posts/edit', comment)"
      ref="comment-form"
    />
  </div>
</template>

<script lang="ts">
import Vue from 'vue';
import {mixin as clickaway} from "vue-clickaway";
import {mapGetters} from "vuex";
import store from "../../store/index";
import VueFlag from "../flags/flag.vue";
import {default as mixins} from '../mixins/user.js';
import VueUserName from '../user-name.vue';
import VueCommentForm from "./comment-form.vue";

export default Vue.extend({
  name: 'comment',
  mixins: [clickaway, mixins],
  components: {
    'vue-username': VueUserName,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
  },
  props: {
    comment: {
      type: Object,
      required: true,
    },
  },
  data() {
    return {
      isEditing: false,
    };
  },
  computed: {
    ...mapGetters('topics', ['topic']),
    anchor() {
      return `comment-${this.comment.id}`;
    },
    highlight() {
      return '#' + this.anchor === window.location.hash;
    },
    flags() {
      return [
        ...store.getters['flags/filter'](this.comment.id, 'Coyote\\Comment'),
        ...store.getters['flags/filter'](this.comment.id, 'Coyote\\Post\\Comment'),
      ];
    },
  },
  methods: {
    edit() {
      this.$store.commit('posts/edit', this.comment);

      if (this.comment.is_editing) {
        this.$nextTick(() => this.$refs['comment-form'].focus());
      }
    },
    deleteComment() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć komentarz?',
        okLabel: 'Tak, usuń',
      })
        .then(() => this.$store.dispatch('posts/deleteComment', this.comment));
    },
    migrate() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Zamienić na post?',
        okLabel: 'Tak, zamień',
      })
        .then(() => {
          this.$store
            .dispatch('posts/migrateComment', this.comment)
            .then(response => window.location.href = response.data.url);
        });
    },
  },
});
</script>
