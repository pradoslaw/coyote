<template>
  <div :id="'comment-' + comment.id" class="comment">
    <div class="media" :class="{author: comment.is_author}">
      <div class="mr-2">
        <img :src="comment.user.photo" class="img-thumbnail media-object">
      </div>

      <div class="media-body">
        <div class="dropdown float-right" v-if="comment.editable">
          <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
            <span class="caret"></span>
          </button>

          <div class="dropdown-menu dropdown-menu-right">
            <a @click="edit" href="javascript:" class="btn-edit dropdown-item" :data-id="comment.id"><i class="fa fa-edit fa-fw"></i> Edytuj</a>
            <a @click="deleteComment(true)" class="dropdown-item" href="javascript:" :data-target="'#modal-confirm' + comment.id" data-toggle="modal"><i class="fa fa-remove fa-fw"></i> Usuń</a>
          </div>
        </div>

        <h5>
          <a v-if="comment.user_id" :href="comment.user.profile" :data-user-id="comment.user.id">{{ comment.user.name }}</a>
          <span v-else>{{ comment.user.name }}</span>
        </h5>

        <h6><a :href="'#comment-' + comment.id" class="text-muted timestamp" :data-timestamp="comment.timestamp">{{ comment.created_at }}</a></h6>

        <div class="margin-sm-top margin-sm-bottom" v-if="!isEditing" v-html="comment.html">
          {{ comment.html }}
        </div>

        <div class="margin-sm-top" v-if="isEditing">
          <form method="post" :action="comment.route.edit" ref="updateForm" @submit.prevent="updateForm">
            <div class="form-group row">
              <textarea-autosize
                name="text"
                class="form-control"
                ref="submitText"
                v-model="comment.text"
                :min-height="40"
                :max-height="350"
                @keydown.native.ctrl.enter="updateForm"
                rows="1"
                tabindex="1"
              ></textarea-autosize>
            </div>

            <div class="form-group row">
              <button type="submit" class="btn btn-primary btn-sm float-right margin-xs-left">Zapisz</button>
              <button type="button" class="btn btn-danger btn-sm float-right" @click="isEditing = false">Anuluj</button>
            </div>

            <div class="clearfix"></div>
          </form>
        </div>

        <ul class="list-inline list-inline-bullet mb-0">
          <li class="list-inline-item"><a @click="reply" href="javascript:" class="text-muted">Odpowiedz</a></li>
          <li class="list-inline-item">
            <a :href="comment.route.flag" :data-url="comment.flag.url" :data-metadata="comment.flag.metadata" class="btn-report text-muted">Zgłoś</a>
          </li>
        </ul>
      </div>
    </div>

    <div class="comment">
      <div v-if="isReplying">
        <form method="post" :action="comment.route.reply" @submit.prevent="replyForm" ref="replyForm">
          <input type="hidden" name="parent_id" :value="parentId">

          <div class="form-group">
            <textarea-autosize
              name="text"
              class="form-control"
              ref="replyText"
              :min-height="40"
              :max-height="350"
              @keydown.native.ctrl.enter="replyForm"
              rows="1"
              tabindex="1"
            ></textarea-autosize>
          </div>

          <div class="row">
            <div class="form-group col-sm-6">
              <input v-if="$store.state.authId === null" type="text" name="email" class="form-control" placeholder="Adres e-mail" tabindex="2">
            </div>

            <div class="form-group col-sm-6">
              <button type="submit" class="btn btn-primary btn-sm float-right margin-xs-left" title="Ctrl+Enter aby opublikować">
                Zapisz
              </button>
              <button type="button" class="btn btn-danger btn-sm float-right" @click="isReplying = false">Anuluj
              </button>
            </div>
          </div>
        </form>
      </div>
    </div>

    <vue-comment
      v-for="child in comment.children"
      :comment="child"
      :key="child.id"
      :nested="true"
    ></vue-comment>

    <vue-modal ref="error">
      {{ error }}
    </vue-modal>

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
  import VueModal from './modal.vue';

  export default {
    name: 'vue-comment', // required with recursive component
    props: ['comment', 'nested'],
    components: {
      'vue-modal': VueModal
    },
    data() {
      return {
        isEditing: false,
        isReplying: false,
        error: ''
      }
    },
    methods: {
      edit() {
        this.isEditing = !this.isEditing;

        if (this.isEditing) {
          this.$nextTick(function () {
            this.$refs.submitText.$el.focus();
          })
        }
      },

      reply() {
        this.isReplying = !this.isReplying;

        if (this.isReplying) {
          this.$nextTick(function () {
            this.$refs.replyText.$el.focus();
          });
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
          .catch(error => {
            this._showError(error);
          });
      },

      scrollIntoView(data) {
        this.$nextTick(function () {
          let el = document.getElementById(`comment-${data.id}`);
          el.scrollIntoView(true);

          window.scrollBy(0, -100);
        });
      },

      _showError(error) {
        let errors = error.response.data.errors;

        this.error = errors[Object.keys(errors)[0]][0];
        this.$refs.error.open();
      }
    },
    computed: {
      parentId() {
        return this.comment.parent_id ? this.comment.parent_id : this.comment.id;
      }
    }
  }
</script>
