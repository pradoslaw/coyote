<template>
  <div :id="'comment-' + comment.id" class="comment">
    <div class="media" :class="{author: comment.is_owner}">
      <div class="me-2">
        <a v-profile="comment.user.id">
          <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="img-thumbnail media-object i-38"></vue-avatar>
        </a>
      </div>

      <div class="media-body">
        <div class="dropdown float-end" v-if="comment.permissions.update">
          <button class="btn btn-xs border-0 text-muted mt-2" type="button" data-bs-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis"></i></button>

          <div class="dropdown-menu dropdown-menu-end">
            <a @click="edit" href="javascript:" class="dropdown-item">
              <i class="fa fa-pen-to-square fa-fw"></i>
              Edytuj
            </a>
            <a @click="deleteComment" class="dropdown-item" href="javascript:">
              <i class="fa fa-fw fa-trash-can"></i>
              Usuń
            </a>
          </div>
        </div>

        <h5>
          <vue-username v-if="comment.user.id" :user="comment.user"></vue-username>
          <span v-else>{{ comment.user.name }}</span>
        </h5>

        <h6><a :href="'#comment-' + comment.id" class="text-muted">
          <vue-timeago :datetime="comment.created_at"></vue-timeago>
        </a></h6>

        <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"></vue-flag>

        <div class="mt-2" v-if="!isEditing" v-html="comment.html"></div>

        <div class="mt-2" v-if="isEditing">
          <vue-markdown
            v-model="comment.text"
            @save="saveComment(comment)"
            ref="submitText"
            preview-url="/Mikroblogi/Preview"
          />

          <div class="d-flex mt-2 justify-content-end">
            <button type="button" class="btn btn-danger btn-sm me-1" @click="isEditing = false">Anuluj</button>
            <vue-button :disabled="isSubmitting" @click.native="saveComment(comment)" class="btn btn-primary btn-sm">Zapisz</vue-button>
          </div>
        </div>

        <ul class="list-inline list-inline-bullet mb-0">
          <li class="list-inline-item">
            <a @click="checkAuth(reply)" href="javascript:" class="text-muted">Odpowiedz</a>
          </li>
          <li v-if="isAuthorized" class="list-inline-item">
            <a href="javascript:" :data-metadata="comment.metadata" :data-url="comment.url" class="btn-report text-muted">Zgłoś</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="comment">
      <div v-if="isReplying">
        <vue-markdown
          v-model="replyForm.text"
          @save="saveComment(replyForm)"
          ref="replyText"
          preview-url="/Mikroblogi/Preview"
        />

        <div class="d-flex mt-2 justify-content-end">
          <button type="button" class="btn btn-danger btn-sm me-1" @click="isReplying = false">Anuluj</button>

          <vue-button @click.native="saveComment(replyForm)" :disabled="isSubmitting" type="submit" class="btn btn-primary btn-sm" title="Ctrl+Enter aby opublikować">
            Zapisz
          </vue-button>
        </div>
      </div>
    </div>

    <vue-comment
      v-for="child in comment.children"
      :comment="child"
      :key="child.id"
      :nested="true"
    ></vue-comment>
  </div>
</template>

<script>
import {mapGetters} from 'vuex';
import VueAvatar from '../avatar.vue';
import VueFlag from '../flags/flag.vue';
import VueButton from '../forms/button.vue';
import VueMarkdown from '../forms/markdown.vue';
import {default as mixins} from '../mixins/user';
import VueModal from '../modal.vue';
import VueUserName from '../user-name.vue';

export default {
  name: 'vue-comment', // required with recursive component
  props: ['comment', 'nested'],
  components: {
    'vue-modal': VueModal,
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-button': VueButton,
    'vue-flag': VueFlag,
    'vue-markdown': VueMarkdown,
  },
  mixins: [mixins],
  data() {
    return {
      isEditing: false,
      isReplying: false,
      isSubmitting: false,
      replyForm: {
        text: '',
        parent_id: this.comment.parent_id ? this.comment.parent_id : this.comment.id,
      },
    }
  },
  methods: {
    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        this.$nextTick(() => this.$refs.submitText.focus());
      }
    },

    reply() {
      this.isReplying = !this.isReplying;

      if (this.isReplying) {
        this.$nextTick(() => this.$refs.replyText.focus());
      }
    },

    deleteComment() {
      this.$confirm({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć komentarz?',
        okLabel: 'Tak, usuń',
      })
        .then(() => this.$store.dispatch('comments/delete', this.comment));
    },

    saveComment(comment) {
      this.isSubmitting = true;

      this.$store.dispatch('comments/save', comment)
        .then(response => {
          this.isEditing = false;
          this.isReplying = false;
          this.replyForm.text = '';

          this.scrollIntoView(response.data);
        })
        .finally(() => this.isSubmitting = false);
    },

    scrollIntoView(comment) {
      this.$nextTick(() => window.location.hash = `comment-${comment.id}`);
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),

    flags() {
      return [
        ...this.store.getters['flags/filter'](this.comment.id, 'Coyote\\Comment'),
        ...this.store.getters['flags/filter'](this.comment.id, 'Coyote\\Post\\Comment'),
      ];
    },
  },
}
</script>
