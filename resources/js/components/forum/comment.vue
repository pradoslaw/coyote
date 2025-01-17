<template>
  <div :id="anchor"
       :class="{'highlight-flash': highlight, 'not-read': comment.is_read === false}"
       class="post-comment"
       v-if="!authorBlocked">
    <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
    <vue-comment-form
      v-if="comment.is_editing"
      :comment="comment"
      @save="$store.commit('posts/editEnd', comment)"
      @cancel="$store.commit('posts/editEnd', comment)"
      ref="comment-form"
    />
    <template v-else>
      <div class="d-flex">
        <div>
          <div class="neon-avatar-border">
            <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="i-35"/>
          </div>
        </div>
        <div class="ms-2">
          <div>
            <vue-username :user="comment.user" :owner="comment.user.id === topic.owner_id"/>
            {{ ' ' }}
            <a :href="comment.url">
              <vue-timeago :datetime="comment.created_at" class="text-muted small"/>
            </a>
            <a v-if="comment.editable" @click="edit" href="javascript:" title="Edytuj ten komentarz" class="btn-comment">
              <vue-icon name="postCommentEdit"/>
            </a>
            <a v-if="comment.editable" @click="deleteComment" href="javascript:" title="Usuń ten komentarz" class="btn-comment">
              <vue-icon name="postCommentDelete"/>
            </a>
            <a v-if="comment.editable" @click="migrate" href="javascript:" title="Zamień w post" class="btn-comment">
              <vue-icon name="postCommentConvertToPost"/>
            </a>
            <a :data-metadata="comment.metadata" :data-url="comment.url" title="Zgłoś ten komentarz" href="javascript:" class="btn-comment">
              <vue-icon name="postCommentReport"/>
            </a>
          </div>
          <span v-html="comment.html" class="comment-text neon-contains-a-color-link"/>
        </div>
      </div>
    </template>
  </div>
  <div class="post-comment comment-delete" v-else>
    <vue-icon name="postCommentAuthorBlocked"/>
    Treść komentarza została ukryta, ponieważ autorem jest zablokowany przez Ciebie użytkownik.
  </div>
</template>

<script lang="ts">
import {mapGetters} from "vuex";
import {confirmModal} from "../../plugins/modals";
import {VueTimeAgo} from "../../plugins/timeago.js";
import store from "../../store/index";
import {nextTick} from "../../vue";
import VueAvatar from "../avatar.vue";
import VueFlag from "../flags/flag.vue";
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user.js';
import VueUserName from '../user-name.vue';
import VueCommentForm from "./comment-form.vue";

export default {
  name: 'comment',
  mixins: [mixins],
  components: {
    VueIcon,
    'vue-username': VueUserName,
    'vue-avatar': VueAvatar,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
    'vue-timeago': VueTimeAgo,
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
    authorBlocked(): boolean {
      return store.getters['user/isBlocked'](this.comment.user.id);
    },
  },
  methods: {
    edit() {
      store.commit('posts/editStart', this.comment);
      nextTick(() => this.$refs['comment-form'].focus());
    },
    deleteComment() {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć komentarz?',
        okLabel: 'Tak, usuń',
      })
        .then(() => store.dispatch('posts/deleteComment', this.comment));
    },
    migrate() {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Zamienić na post?',
        okLabel: 'Tak, zamień',
      })
        .then(() => {
          store
            .dispatch('posts/migrateComment', this.comment)
            .then(response => window.location.href = response.data.url);
        });
    },
  },
};
</script>
