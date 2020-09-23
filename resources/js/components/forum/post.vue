<template>
  <div :id="anchor" :class="{'is-deleted': post.deleted_at, 'highlight-flash': highlight}" class="card card-post">
    <a v-if="post.deleted_at" @click="isCollapsed = !isCollapsed" href="javascript:" class="post-delete card-body text-decoration-none">
      <i class="fas fa-warning"></i>

      Post usunięty <vue-timeago :datetime="post.deleted_at"></vue-timeago>

      <template v-if="post.deleter_name">
        przez {{ post.deleter_name }}
      </template>.

      <template v-if="post.delete_reason">
        Powód: {{ post.delete_reason }}.
      </template>
    </a>

    <div :class="{'collapse': isCollapsed}" class="card-header">
      <div class="row d-none d-lg-flex">
        <div class="col-2">
          <h5 class="mb-0 post-author">
            <vue-user-name v-if="post.user" :user="post.user"></vue-user-name>
            <span v-else>{{ post.user_name }}</span>
          </h5>
        </div>

        <div class="col-10 text-truncate small">
          <i class="far fa-file"></i>

          <a :href="post.url" class="small text-body">
            <vue-timeago :datetime="post.created_at"></vue-timeago>
          </a>

          <small v-if="post.ip" :title="post.ip" class="text-muted">{{ post.ip }}</small>
          <small v-if="post.browser" :title="post.ip" class="text-muted">{{ post.browser }}</small>
        </div>
      </div>

    </div>

    <div :class="{'collapse': isCollapsed}" class="card-body">
      <div class="media d-lg-none">
        <div class="media-left mr-2">
          <vue-avatar v-if="post.user" :id="post.user.id" :name="post.user.name" :photo="post.user.photo" class="d-block i-35 img-thumbnail"></vue-avatar>
        </div>

        <div class="media-body">
          <h5 class="mb-0 post-author">
            <vue-user-name v-if="post.user" :user="post.user"></vue-user-name>
            <span v-else>{{ post.user_name }}</span>
          </h5>

          <a :href="post.url" class="text-muted small">
            <vue-timeago :datetime="post.created_at"></vue-timeago>

            <small v-if="post.ip" :title="post.ip" class="post-ip">({{ post.ip }})</small>
          </a>
        </div>
      </div>

      <div class="row">
        <div class="d-none d-lg-block col-lg-2">
          <template v-if="post.user">
            <vue-avatar v-if="post.user" :id="post.user.id" :name="post.user.name" :photo="post.user.photo" class="post-avatar img-thumbnail"></vue-avatar>

            <span v-if="post.user.group" class="badge badge-secondary mb-1">{{ post.user.group }}</span>

            <ul class="post-stats list-unstyled">
              <li>
                <strong>Rejestracja:</strong>
                <small>{{ formatDistanceToNow(post.user.created_at) }}</small>
              </li>

              <li>
                <strong>Ostatnio:</strong>
                <small>{{ formatDistanceToNow(post.user.visited_at) }}</small>
              </li>

              <li v-if="post.user.location">
                <strong>Lokalizacja:</strong>
                <small>{{ post.user.location }}</small>
              </li>

              <li v-if="post.user.allow_count">
                <strong>Postów:</strong>
                <small><a title="Znajdź posty tego użytkownika" :href="`/Forum/User/${post.user.id}`" style="text-decoration: underline">{{ post.user.posts }}</a></small>
              </li>
            </ul>
          </template>
        </div>

        <div v-if="!isEditing" class="col-12 col-lg-10">
          <div class="post-vote">
            <strong class="vote-count" title="Ocena postu">{{ post.score }}</strong>

            <a v-if="!post.deleted_at" :class="{'on': post.is_voted}" @click="vote(post)" class="vote-up" href="javascript:" title="Kliknij, jeżeli post jest wartościowy (kliknij ponownie, aby cofnąć)">
              <i class="far fa-thumbs-up fa-fw"></i>
              <i class="fas fa-thumbs-up fa-fw"></i>
            </a>

            <a v-if="!post.deleted_at && isAcceptAllowed" :class="{'on': post.is_accepted}" @click="accept(post)" class="vote-accept" href="javascript:" title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną (kliknij ponownie, aby cofnąć)">
              <i class="fas fa-check fa-fw"></i>
            </a>
          </div>

          <div class="post-content">
            <div v-html="post.html"></div>

            <div v-if="tags" class="padding-sm-top padding-sm-bottom">
              <ul class="tag-clouds">
                <li v-for="tag in tags"><a :href="tag.url">{{ tag.name }}</a></li>
              </ul>
            </div>

            <ul v-if="post.attachments" class="list-unstyled list-attachments">
              <li v-for="attachment in post.attachments">
                <i class="fas fa-download"></i>

                <a :href="attachment.url">{{ attachment.name }}</a>
                <small>({{ Math.round(attachment.size / 1024 / 1024, 2) }} MB) - <em>ściągnięć: {{ attachment.count }}</em></small>
              </li>
            </ul>

            <template v-if="post.user && post.user.sig">
              <hr>

              <footer v-html="post.user.sig"></footer>
            </template>
          </div>

          <div v-if="post.edit_count" class="edit-info">
            <strong>
              <a class="btn-history" :title="post.permissions.update ? 'Zobacz historię zmian tego postu' : ''" :href="post.permissions.update ? `/Forum/Post/Log/${post.id}` : ''">
                <i class="fas fa-external-link-alt"></i>
              </a>

              edytowany {{ post.edit_count }}x, ostatnio: <vue-user-name :user="post.editor"></vue-user-name>
            </strong>

            <vue-timeago :datetime="post.updated_at"></vue-timeago>
          </div>

          <div class="post-comments">
            <vue-comment v-for="comment in post.comments" :key="comment.id" :comment="comment"></vue-comment>

            <vue-comment-form v-show="isCommenting" :comment="commentDefault" @save="isCommenting = false" @cancel="isCommenting = false" ref="comment-form"></vue-comment-form>
          </div>
        </div>

        <vue-form
          v-else
          ref="form"
          class="col-12 col-lg-10 mt-2 mb-2"
          :post="post"
          :show-title-input="post.id === topic.first_post_id"
          :show-tags-input="post.id === topic.first_post_id"
          :show-sticky-checkbox="post.id === topic.first_post_id && post.permissions.sticky"
          :upload-mimes="uploadMimes"
          :upload-max-size="uploadMaxSize"
          @cancel="isEditing = false"
          @save="isEditing = false"
        ></vue-form>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">
          <div v-if="!post.deleted_at">
            <button @click="subscribe(post)" class="btn btn-sm">
              <i :class="{'fas text-primary': post.is_subscribed, 'far': !post.is_subscribed}" class="fa-fw fa-bell"></i>

              <span class="d-none d-sm-inline">Obserwuj</span>
            </button>

            <button @click="copy" class="btn btn-sm">
              <i class="fas fa-fw fa-share-alt"></i> <span class="d-none d-sm-inline">Udostępnij</span>
            </button>

            <button @click="comment" class="btn btn-sm">
              <i class="far fa-fw fa-comment"></i> <span class="d-none d-sm-inline">Komentuj</span>
            </button>
          </div>

          <div v-if="post.permissions.write" class="ml-auto">
            <button v-if="post.permissions.update && !post.deleted_at" @click="edit" class="btn btn-sm">
              <i class="fa fa-fw fa-edit"></i> <span class="d-none d-sm-inline">Edytuj</span>
            </button>

            <template v-if="post.permissions.delete">
              <button v-if="!post.deleted_at" @click="deletePost(true)" class="btn btn-sm">
                <i class="fa fa-fw fa-times"></i> <span class="d-none d-sm-inline">Usuń</span>
              </button>
              <button v-else class="btn btn-sm" @click="restore">
                <i class="fa fa-fw- fa-undo"></i> <span class="d-none d-sm-inline">Przywróć</span>
              </button>
            </template>

            <button v-if="!post.deleted_at" @click="$emit('reply', post)" class="btn btn-sm">
              <i class="fa fa-fw fa-quote-left"></i> <span class="d-none d-sm-inline">Odpowiedz</span>
            </button>

            <button class="btn btn-sm">
              <i class="fa fa-fw fa-flag"></i> <span class="d-none d-sm-inline">Raportuj</span>
            </button>

            <div v-if="post.permissions.merge || post.permissions.adm_access" class="dropdown float-right">
              <button class="btn btn-sm" data-toggle="dropdown">
                <i class="fas fa-fw fa-ellipsis-h"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-right">
                <a v-if="!post.deleted_at && post.permissions.merge && !firstPost" @click="merge(true)" href="javascript:" class="dropdown-item">
                  <i class="fas fa-compress fa-fw"></i> Połącz z poprzednim
                </a>

                <a v-if="post.permissions.adm_access" class="dropdown-item" :href="`/Adm/Firewall/Save?user=${post.user ? post.user.id : ''}&ip=${post.ip}`">
                  <i class="fas fa-ban fa-fw"></i> Zablokuj użytkownika
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <vue-modal ref="delete-modal">
      Post zostanie usunięty. Czy na pewno chcesz to zrobić?

      <p v-if="post.permissions.delete" class="mt-2"><vue-select name="reason_id" :options="reasons" :value.sync="reasonId" class="form-control-sm" placeholder="-- wybierz --"></vue-select></p>

      <template slot="buttons">
        <button @click="$refs['delete-modal'].close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
        <button @click="deletePost(false)" type="submit" class="btn btn-danger danger">Tak, usuń</button>
      </template>
    </vue-modal>

    <vue-modal ref="merge-modal">
      <p>Czy chcesz połaczyć ten post z poprzednim?</p>

      <template slot="title">Czy chcesz połączyć?</template>

      <template slot="buttons">
        <button @click="$refs['merge-modal'].close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj</button>
        <vue-button @click.native="merge(false)" :disabled="isProcessing" class="btn btn-danger danger">Tak, połącz</vue-button>
      </template>
    </vue-modal>
  </div>
</template>
<script lang="ts">
  import Vue from 'vue';
  import { Prop, Ref } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { Post, Topic, User } from '../../types/models';
  import VueClipboard from '../../plugins/clipboard';
  import VueAvatar from '../avatar.vue';
  import VueUserName from "../user-name.vue";
  import VueComment from './comment.vue';
  import VueForm from  './form.vue';
  import VueCommentForm from "./comment-form.vue";
  import VueSelect from  './../forms/select.vue';
  import VueButton from  './../forms/button.vue';
  import { mapActions, mapGetters, mapState } from "vuex";
  import VueModal from "../modal.vue";
  import formatDistanceToNow from 'date-fns/formatDistanceToNow';
  import pl from 'date-fns/locale/pl';

  Vue.use(VueClipboard);

  @Component({
    name: 'post',
    components: {
      'vue-avatar': VueAvatar,
      'vue-user-name': VueUserName,
      'vue-comment': VueComment,
      'vue-comment-form': VueCommentForm,
      'vue-form': VueForm,
      'vue-modal': VueModal,
      'vue-select': VueSelect,
      'vue-button': VueButton
    },
    methods: mapActions('posts', ['vote', 'accept', 'subscribe']),
    computed: {
      ...mapState('user', {user: state => state}),
      ...mapGetters('user', ['isAuthorized']),
      ...mapGetters('posts', ['posts']),
      ...mapState('posts', ['topic'])
    }
  })
  export default class VuePost extends Vue {
    @Prop(Object)
    post!: Post;

    @Prop({default: 20})
    readonly uploadMaxSize!: number;

    @Prop()
    readonly uploadMimes!: string;

    @Prop()
    readonly reasons!: string[];

    @Ref()
    readonly form!: VueForm;

    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Ref('delete-modal')
    readonly deleteModal!: VueModal;

    @Ref('merge-modal')
    readonly mergeModal!: VueModal;

    isProcessing = false;
    isCollapsed = this.post.deleted_at != null;
    isEditing = false;
    isCommenting = false;
    reasonId = null;

    readonly topic!: Topic;
    readonly isAuthorized! : boolean;
    readonly posts!: Post[];
    readonly user!: User;

    private commentDefault = { text: '', post_id: this.post.id };

    formatDistanceToNow(date) {
      return formatDistanceToNow(new Date(date), { locale: pl });
    }

    copy() {
      if (this.$copy(this.post.url)) {
        this.$notify({type: 'success', text: 'Link prawidłowo skopiowany do schowka.'});
      }
      else {
        this.$notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    }

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        // @ts-ignore
        this.$nextTick(() => this.form.textarea.focus());
      }
    }

    comment() {
      this.isCommenting = !this.isCommenting;

      if (this.isCommenting) {
        // @ts-ignore
        this.$nextTick(() => this.commentForm.textarea.focus());
      }
    }

    deletePost(confirm = false) {
      if (confirm) {
        this.deleteModal.open();
      }
      else {
        this.deleteModal.close();
        this.$store.dispatch('posts/delete', { post: this.post, reasonId: this.reasonId }).then(() => this.isCollapsed = true);
      }
    }

    merge(confirm = false) {
      if (confirm) {
        this.mergeModal.open();
      }
      else {
        this.isProcessing = true;

        this.$store.dispatch('posts/merge', this.post).finally(() => {
          this.isProcessing = false;

          this.mergeModal.close();
        });
      }
    }

    restore() {
      this.isCollapsed = false;
      this.$store.dispatch('posts/restore', this.post);
    }

    get firstPost() {
      return this.posts[Object.keys(this.posts)[0]];
    }

    get tags() {
      return this.post.id === this.firstPost.id ? this.topic.tags : [];
    }

    get isAcceptAllowed() {
      if (!this.isAuthorized) {
        return false;
      }

      // user can't accept first post in topic
      return (this.user.id === this.firstPost.user_id || this.post.permissions.update) && this.post.id !== this.firstPost.id;
    }

    get anchor() {
      return `id${this.post.id}`;
    }

    get highlight() {
      return '#' + this.anchor === window.location.hash;
    }
  }
</script>

