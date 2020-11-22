<template>
  <div :id="'comment-' + comment.id" class="comment">
    <div class="media" :class="{author: comment.is_author}">
      <div class="mr-2">
        <a v-profile="comment.user.id">
          <vue-avatar :photo="comment.user.photo" :name="comment.user.name" :id="comment.user.id" class="img-thumbnail media-object"></vue-avatar>
        </a>
      </div>

      <div class="media-body">
        <div class="dropdown float-right" v-if="comment.editable">
          <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
          </button>

          <div class="dropdown-menu dropdown-menu-right">
            <a @click="edit" href="javascript:" class="btn-edit dropdown-item"><i class="fa fa-edit fa-fw"></i> Edytuj</a>
            <a @click="deleteComment(true)" class="dropdown-item" href="javascript:"><i class="fa fa-trash fa-fw"></i> Usuń</a>
          </div>
        </div>

        <h5>
          <vue-username v-if="comment.user.id" :user="comment.user"></vue-username>
          <span v-else>{{ comment.user.name }}</span>
        </h5>

        <h6><a :href="'#comment-' + comment.id" class="text-muted"><vue-timeago :datetime="comment.created_at"></vue-timeago></a></h6>

        <div class="mt-2" v-if="!isEditing" v-html="comment.html">
          {{ comment.html }}
        </div>

        <div class="mt-2" v-if="isEditing">
          <div class="form-group row">
            <textarea
              v-autosize
              name="text"
              class="form-control"
              ref="submitText"
              v-model="comment.text"
              @keydown.ctrl.enter="updateForm"
              rows="1"
              tabindex="1"
            ></textarea>
          </div>

          <div class="form-group row">
            <vue-button :disabled="isSubmitting" class="btn btn-primary btn-sm float-right ml-1">Zapisz</vue-button>
            <button type="button" class="btn btn-danger btn-sm float-right" @click="isEditing = false">Anuluj</button>
          </div>

          <div class="clearfix"></div>
        </div>

        <ul class="list-inline list-inline-bullet mb-0">
          <li class="list-inline-item"><a @click="reply" href="javascript:" class="text-muted">Odpowiedz</a></li>
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
            name="text"
            class="form-control"
            ref="replyText"
            @keydown.ctrl.enter="replyForm"
            rows="1"
            tabindex="1"
          ></textarea>
        </div>

        <div class="row">
          <div class="form-group col-sm-6">
            <input v-if="!isAuthorized" type="text" name="email" class="form-control" placeholder="Adres e-mail" tabindex="2">
          </div>

          <div class="form-group col-sm-6">
            <button type="submit" class="btn btn-primary btn-sm float-right ml-1" title="Ctrl+Enter aby opublikować">
              Zapisz
            </button>
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
  import axios from 'axios';
  import VueModal from '../modal.vue';
  import VueAvatar from '../avatar.vue';
  import VueUserName from '../user-name.vue';
  import VueButton from '../forms/button.vue';
  import { default as mixins } from '../mixins/user';
  import { mapGetters } from 'vuex';

  export default {
    name: 'vue-comment', // required with recursive component
    props: ['comment', 'nested'],
    components: {
      'vue-modal': VueModal,
      'vue-avatar': VueAvatar,
      'vue-username': VueUserName,
      'vue-button': VueButton
    },
    mixins: [ mixins ],
    data() {
      return {
        isEditing: false,
        isReplying: false,
        isSubmitting: false
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

          axios.delete(this.comment.route.delete).then(() => {
            this.$store.commit('comments/remove', this.comment);
          });
        }
      },

      updateForm() {
        axios.post(this.$refs.updateForm.action, new FormData(this.$refs.updateForm))
          .then(response => {
            this.$store.commit('comments/update', response.data);
            this.isEditing = false;
          })
          .catch(function (error) {
            this._showError(error);
          });
      },

      replyForm() {
        axios.post(this.$refs.replyForm.action, new FormData(this.$refs.replyForm))
          .then(response => {
            this.$store.commit('comments/reply', response.data);
            this.isReplying = false;

            this.scrollIntoView(response.data);
          })
      },

      scrollIntoView(data) {
        this.$nextTick(function () {
          let el = document.getElementById(`comment-${data.id}`);
          el.scrollIntoView(true);

          window.scrollBy(0, -100);
        });
      }
    },
    computed: {
      parentId() {
        return this.comment.parent_id ? this.comment.parent_id : this.comment.id;
      },

      ...mapGetters('user', ['isAuthorized'])
    }
  }
</script>
