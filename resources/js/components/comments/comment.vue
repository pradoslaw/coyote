<template>
  <div :id="'comment-' + comment.id" class="comment">
    <div class="media" :class="{author: comment.is_owner}">
      <div class="me-2">
        <a v-profile="comment.user.id">
          <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="img-thumbnail media-object i-38"/>
        </a>
      </div>
      <div class="media-body">
        <div class="dropdown float-end" v-if="comment.permissions.update">
          <button class="btn btn-xs border-0 text-muted mt-2" type="button" data-bs-toggle="dropdown" aria-label="Dropdown">
            <vue-icon name="jobOfferCommentMenuDropdown"/>
          </button>
          <div class="dropdown-menu dropdown-menu-end">
            <a @click="edit" href="javascript:" class="dropdown-item">
              <vue-icon name="jobOfferCommentEdit"/>
              Edytuj
            </a>
            <a @click="deleteComment" class="dropdown-item" href="javascript:">
              <vue-icon name="jobOfferCommentDelete"/>
              Usuń
            </a>
          </div>
        </div>
        <h5>
          <vue-username v-if="comment.user.id" :user="comment.user"/>
          <span v-else>{{ comment.user.name }}</span>
        </h5>
        <h6>
          <a :href="'#comment-' + comment.id" class="text-muted">
            <vue-timeago :datetime="comment.created_at"/>
          </a>
        </h6>
        <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
        <div class="mt-2" v-if="!isEditing" v-html="comment.html"/>
        <div class="mt-2" v-if="isEditing">
          <vue-markdown
            v-model="comment.text"
            @save="saveComment(comment)"
            ref="submitText"
            preview-url="/Mikroblogi/Preview"
            :emojis="emojis"
          />
          <div class="d-flex mt-2 justify-content-end">
            <button type="button" class="btn btn-danger btn-sm me-1" @click="isEditing = false">
              Anuluj
            </button>
            <vue-button :disabled="isSubmitting" @click="saveComment(comment)" class="btn btn-primary btn-sm">
              Zapisz
            </vue-button>
          </div>
        </div>
        <ul class="list-inline list-inline-bullet mb-0">
          <li class="list-inline-item">
            <a @click="checkAuth(reply)" href="javascript:" class="text-muted">Odpowiedz</a>
          </li>
          <li v-if="isAuthorized" class="list-inline-item">
            <span :data-metadata="comment.metadata" :data-url="comment.url" class="btn-report text-muted">
              Zgłoś
            </span>
          </li>
        </ul>
      </div>
    </div>
    <div class="comment">
      <div v-if="isReplying">
        <vue-markdown
          v-model="replyForm.text"
          @save="saveComment(replyForm)"
          :emojis="emojis"
          ref="replyText"
          preview-url="/Mikroblogi/Preview"
        />
        <div class="d-flex mt-2 justify-content-end">
          <button type="button" class="btn btn-danger btn-sm me-1" @click="isReplying = false">Anuluj</button>
          <vue-button
            @click="saveComment(replyForm)"
            :disabled="isSubmitting"
            type="submit"
            class="btn btn-primary btn-sm"
            title="Ctrl+Enter aby opublikować">
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
    />
  </div>
</template>

<script>
import {mapGetters} from 'vuex';

import {confirmModal} from '../../plugins/modals';
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from '../../store/index';
import {nextTick} from '../../vue';
import VueAvatar from '../avatar.vue';
import VueFlag from '../flags/flag.vue';
import VueButton from '../forms/button.vue';
import VueMarkdown from '../forms/markdown.vue';
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user';
import VueModal from '../modal.vue';
import VueUserName from '../user-name.vue';

export default {
  name: 'vue-comment', // required with recursive component
  props: ['comment', 'nested'],
  components: {
    'vue-avatar': VueAvatar,
    'vue-button': VueButton,
    'vue-flag': VueFlag,
    'vue-icon': VueIcon,
    'vue-markdown': VueMarkdown,
    'vue-modal': VueModal,
    'vue-timeago': VueTimeAgo,
    'vue-username': VueUserName,
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
      emojis: {},
    };
  },
  created() {
    this.emojis = window.emojis;
  },
  methods: {
    edit() {
      this.isEditing = !this.isEditing;
      if (this.isEditing) {
        nextTick(() => this.$refs.submitText.focus());
      }
    },

    reply() {
      this.isReplying = !this.isReplying;
      if (this.isReplying) {
        nextTick(() => this.$refs.replyText.focus());
      }
    },

    deleteComment() {
      confirmModal({
        message: 'Tej operacji nie będzie można cofnąć.',
        title: 'Usunąć komentarz?',
        okLabel: 'Tak, usuń',
      })
        .then(() => store.dispatch('comments/delete', this.comment));
    },

    saveComment(comment) {
      this.isSubmitting = true;
      store.dispatch('comments/save', comment)
        .then(response => {
          this.isEditing = false;
          this.isReplying = false;
          this.replyForm.text = '';
          this.scrollIntoView(response.data);
        })
        .finally(() => this.isSubmitting = false);
    },

    scrollIntoView(comment) {
      nextTick(() => window.location.hash = `comment-${comment.id}`);
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    flags() {
      return [
        ...store.getters['flags/filter'](this.comment.id, 'Coyote\\Comment'),
        ...store.getters['flags/filter'](this.comment.id, 'Coyote\\Post\\Comment'),
      ];
    },
  },
};
</script>
