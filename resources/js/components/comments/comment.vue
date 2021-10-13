<template>
  <div :id="'comment-' + comment.id" class="comment">
    <div class="media" :class="{author: comment.is_owner}">
      <div class="mr-2">
        <a v-profile="comment.user.id">
          <vue-avatar v-bind="comment.user" :is-online="comment.user.is_online" class="img-thumbnail media-object"></vue-avatar>
        </a>
      </div>

      <div class="media-body">
        <div class="dropdown float-right" v-if="comment.permissions.update">
          <button class="btn btn-xs border-0 text-muted mt-2" type="button" data-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis-h"></i></button>

          <div class="dropdown-menu dropdown-menu-right">
            <a @click="edit" href="javascript:" class="dropdown-item"><i class="fa fa-edit fa-fw"></i> Edytuj</a>
            <a @click="deleteComment(true)" class="dropdown-item" href="javascript:"><i class="fa fa-trash fa-fw"></i> Usuń</a>
          </div>
        </div>

        <h5>
          <vue-username v-if="comment.user.id" :user="comment.user"></vue-username>
          <span v-else>{{ comment.user.name }}</span>
        </h5>

        <h6><a :href="'#comment-' + comment.id" class="text-muted"><vue-timeago :datetime="comment.created_at"></vue-timeago></a></h6>

        <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"></vue-flag>

        <div class="mt-2" v-if="!isEditing" v-html="comment.html">
          {{ comment.html }}
        </div>

        <div class="mt-2" v-if="isEditing">
          <div class="form-group">
            <textarea
              v-autosize
              name="text"
              class="form-control"
              ref="submitText"
              v-model="comment.text"
              @keydown.ctrl.enter="saveComment(comment)"
              rows="1"
              tabindex="1"
            ></textarea>
          </div>

          <div class="row">
            <div class="form-group col-12">
              <vue-button :disabled="isSubmitting" @click.native="saveComment(comment)" class="btn btn-primary btn-sm float-right ml-1">Zapisz</vue-button>
              <button type="button" class="btn btn-danger btn-sm float-right" @click="isEditing = false">Anuluj</button>
            </div>
          </div>

          <div class="clearfix"></div>
        </div>

        <ul class="list-inline list-inline-bullet mb-0">
          <li class="list-inline-item"><a @click="checkAuth(reply)" href="javascript:" class="text-muted">Odpowiedz</a></li>
          <li v-if="isAuthorized" class="list-inline-item">
            <a href="javascript:" :data-metadata="comment.metadata" :data-url="comment.url"  class="btn-report text-muted">Zgłoś</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="comment">
      <div v-if="isReplying">
        <div class="form-group">
          <textarea
            v-autosize
            v-model="replyForm.text"
            class="form-control"
            ref="replyText"
            @keydown.ctrl.enter="saveComment(replyForm)"
            rows="1"
            tabindex="1"
          ></textarea>
        </div>

        <div class="row">
          <div class="form-group col-12">
            <vue-button @click.native="saveComment(replyForm)" :disabled="isSubmitting" type="submit" class="btn btn-primary btn-sm float-right ml-1" title="Ctrl+Enter aby opublikować">
              Zapisz
            </vue-button>
            <button type="button" class="btn btn-danger btn-sm float-right" @click="isReplying = false">Anuluj
            </button>
          </div>
        </div>
      </div>
    </div>

    <vue-comment
      v-for="child in comment.children"
      :comment="child"
      :key="child.id"
      :nested="true"
    ></vue-comment>

    <vue-modal ref="confirm">
      Czy na pewno chcesz usunąć ten komentarz?

      <template slot="buttons">
        <button @click="$refs.confirm.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj
        </button>
        <button @click="deleteComment(false)" type="submit" class="btn btn-danger danger">Tak, usuń</button>
      </template>
    </vue-modal>
  </div>
</template>

<script>
  import VueModal from '../modal.vue';
  import VueAvatar from '../avatar.vue';
  import VueUserName from '../user-name.vue';
  import VueButton from '../forms/button.vue';
  import VueFlag from '../flags/flag.vue';
  import { default as mixins } from '../mixins/user';
  import { mapGetters } from 'vuex';

  export default {
    name: 'vue-comment', // required with recursive component
    props: ['comment', 'nested'],
    components: {
      'vue-modal': VueModal,
      'vue-avatar': VueAvatar,
      'vue-username': VueUserName,
      'vue-button': VueButton,
      'vue-flag': VueFlag
    },
    mixins: [ mixins ],
    data() {
      return {
        isEditing: false,
        isReplying: false,
        isSubmitting: false,
        replyForm: {
          text: '',
          parent_id: this.comment.parent_id ? this.comment.parent_id : this.comment.id
        }
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

      deleteComment(confirm) {
        if (confirm) {
          this.$refs.confirm.open();
        } else {
          this.$refs.confirm.close();

          this.$store.dispatch('jobs/deleteComment', this.comment);
        }
      },

      saveComment(comment) {
        this.isSubmitting = true;

        this.$store.dispatch('jobs/saveComment', comment)
          .then(response => {
            this.isEditing = false;
            this.isReplying = false;

            this.scrollIntoView(response.data);
          })
          .finally(() => this.isSubmitting = false);
      },

      scrollIntoView(comment) {
        this.$nextTick(() => window.location.hash = `comment-${comment.id}`);
      }
    },
    computed: {
      ...mapGetters('user', ['isAuthorized']),

      flags() {
        return this.$store.getters['flags/filter'](this.comment.id, 'Coyote\\Comment');
      }
    }
  }
</script>
