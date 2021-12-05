<template>
  <div :id="anchor" :class="{'is-deleted': post.deleted_at, 'not-read': !post.is_read, 'highlight-flash': highlight}" class="card card-post">
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

    <div :class="{'collapse': isCollapsed, 'd-lg-block': !isCollapsed}" class="card-header d-none ">
      <div class="row">
        <div class="col-2">
          <h5 class="mb-0 post-author">
            <vue-username v-if="post.user" :user="post.user" :owner="post.user_id === topic.owner_id"></vue-username>
            <span v-else>{{ post.user_name }}</span>
          </h5>
        </div>

        <div class="col-10 text-truncate small">
          <i v-if="post.is_read" class="far fa-file"></i>
          <i v-else title="Nowy post" class="not-read"></i>

          <a :href="post.url" class="small text-body">
            <vue-timeago :datetime="post.created_at"></vue-timeago>
          </a>

          <a v-if="post.ip" :href="`/Adm/Stream?ip=${post.ip}`" :title="post.ip" class="text-muted small">{{ post.ip }}</a>
          <small v-if="post.browser" :title="post.browser" class="text-muted">{{ post.browser }}</small>
        </div>
      </div>
    </div>

    <div :class="{'collapse': isCollapsed}" class="card-body">
      <div class="media d-lg-none mb-2">
        <div class="media-left mr-2">
          <vue-avatar
            v-if="post.user"
            :id="post.user.id"
            :name="post.user.name"
            :photo="post.user.photo"
            :is-online="post.user.is_online"
            class="d-block i-35 img-thumbnail"
          ></vue-avatar>
        </div>

        <div class="media-body">
          <h5 class="mb-0 post-author">
            <vue-username v-if="post.user" :user="post.user" :owner="post.user_id === topic.owner_id"></vue-username>
            <span v-else>{{ post.user_name }}</span>
          </h5>

          <a :href="post.url" class="text-muted small">
            <vue-timeago :datetime="post.created_at"></vue-timeago>
          </a>

          <a v-if="post.ip" :href="`/Adm/Stream?ip=${post.ip}`" :title="post.ip" class="text-muted small">
            ({{ post.ip }})
          </a>
        </div>
      </div>

      <div class="row">
        <div class="d-none d-lg-block col-lg-2">
          <template v-if="post.user">
            <vue-avatar
              v-if="post.user"
              :id="post.user.id"
              :name="post.user.name"
              :photo="post.user.photo"
              :is-online="post.user.is_online"
              class="post-avatar img-thumbnail"
            ></vue-avatar>

            <span v-if="post.user.group_name" class="badge badge-secondary mb-1">{{ post.user.group_name }}</span>

            <ul class="post-stats list-unstyled">
              <li>
                <strong>Rejestracja:</strong>
                <small>{{ formatDistanceToNow(post.user.created_at) }}</small>
              </li>

              <li>
                <strong>Ostatnio:</strong>
                <small>{{ formatDistanceToNow(post.user.visited_at ? post.user.visited_at : post.user.created_at) }}</small>
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

        <div v-show="!post.is_editing" class="col-12 col-lg-10">
          <vue-flag v-for="flag in flags" :key="flag.id" :flag.sync="flag"></vue-flag>

          <div class="post-vote">
            <strong class="vote-count" title="Ocena postu">{{ post.score }}</strong>

            <a
              v-if="!post.deleted_at"
              :class="{'on': post.is_voted}"
              :aria-label="voters"
              @click="checkAuth(vote, post)"
              @mouseenter.once="loadVoters(post)"
              data-balloon-pos="left"
              data-balloon-break
              class="vote-up"
              href="javascript:"
            >
              <i class="far fa-thumbs-up fa-fw"></i>
              <i class="fas fa-thumbs-up fa-fw"></i>
            </a>

            <a v-if="!post.deleted_at && post.permissions.accept" :class="{'on': post.is_accepted}" @click="accept(post)" class="vote-accept" href="javascript:" title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną (kliknij ponownie, aby cofnąć)">
              <i class="fas fa-check fa-fw"></i>
            </a>

            <span v-else-if="post.is_accepted" class="vote-accept on">
              <i class="fas fa-check fa-fw"></i>
            </span>
          </div>

          <div class="post-content">
            <div v-html="post.html"></div>

            <ul v-if="post.assets.length" class="list-unstyled mb-1">
              <li v-for="asset in post.assets" class="small">
                <i class="fas fa-download"></i>

                <a :href="`/assets/${asset.id}/${asset.name}`">{{ asset.name }}</a>
                <small>({{ asset.size | size }}) - <em>ściągnięć: {{ asset.count }}</em></small>
              </li>
            </ul>

            <template v-if="post.user && post.user.sig">
              <hr>

              <footer v-html="post.user.sig"></footer>
            </template>
          </div>

          <vue-tags :tags="tags" class="tag-clouds-md mt-2 mb-2"></vue-tags>

          <div v-if="post.edit_count" class="edit-info">
            <strong>
              <a class="btn-history" :title="post.permissions.update ? 'Zobacz historię zmian tego postu' : ''" :href="post.permissions.update ? `/Forum/Post/Log/${post.id}` : ''">
                <i class="fas fa-external-link-alt"></i>
              </a>

              edytowany {{ post.edit_count }}x, ostatnio: <vue-username :user="post.editor"></vue-username>
            </strong>

            <vue-timeago :datetime="post.updated_at"></vue-timeago>
          </div>

          <div class="post-comments">
            <div v-if="post.comments_count > Object.keys(post.comments).length" class="d-inline-block mb-2 show-all-comments">
              <a @click="loadComments(post)" href="javascript:"><i class="far fa-comments"></i> Zobacz {{ totalComments | declination(['pozostały', 'pozostałe', 'pozostałe']) }} {{ totalComments }} {{ totalComments | declination(['komentarz', 'komentarze', 'komentarzy']) }}</a>
            </div>

            <vue-comment
              v-for="comment in post.comments"
              :key="comment.id"
              :comment="comment"
            ></vue-comment>

            <vue-comment-form
              v-show="isCommenting"
              :comment="commentDefault"
              @save="isCommenting = false"
              @cancel="isCommenting = false"
              ref="comment-form"
              class="mt-2"
            ></vue-comment-form>
          </div>
        </div>

        <vue-form
          v-if="post.is_editing"
          ref="form"
          class="col-12 col-lg-10 mt-2 mb-2"
          :post="post"
          :show-title-input="post.id === topic.first_post_id"
          :show-tags-input="post.id === topic.first_post_id"
          :show-sticky-checkbox="post.id === topic.first_post_id && post.permissions.sticky"
          :upload-mimes="uploadMimes"
          :upload-max-size="uploadMaxSize"
          @cancel="$store.commit('posts/edit', post)"
          @save="$store.commit('posts/edit', post)"
        ></vue-form>
      </div>
    </div>

    <div :class="{'collapse': isCollapsed}" class="card-footer">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">
          <div v-if="!post.deleted_at">
            <button @click="checkAuth(subscribe, post)" class="btn btn-sm">
              <i :class="{'fas text-primary': post.is_subscribed, 'far': !post.is_subscribed}" class="fa-fw fa-bell"></i>

              <span class="d-none d-sm-inline">Obserwuj</span>
            </button>

            <button @click="copy" class="btn btn-sm">
              <i class="fas fa-fw fa-share-alt"></i> <span class="d-none d-sm-inline">Udostępnij</span>
            </button>

            <button @click="checkAuth(comment)" class="btn btn-sm">
              <i :class="{'fas text-primary': isCommenting, 'far': !isCommenting}" class="fa-fw fa-comment"></i> <span class="d-none d-sm-inline">Komentuj</span>
            </button>
          </div>

          <div v-if="post.permissions.write" class="ml-auto">
            <button v-if="post.permissions.update && !post.deleted_at" @click="edit" class="btn btn-sm">
              <i :class="{'text-primary': post.is_editing}" class="fas fa-fw fa-edit"></i> <span class="d-none d-sm-inline">Edytuj</span>
            </button>

            <template v-if="post.permissions.delete">
              <button v-if="!post.deleted_at" @click="deletePost(true)" class="btn btn-sm">
                <i class="fa fa-fw fa-times"></i> <span class="d-none d-sm-inline">Usuń</span>
              </button>
              <button v-else class="btn btn-sm" @click="restore">
                <i class="fa fa-fw fa-undo"></i> <span class="d-none d-sm-inline">Przywróć</span>
              </button>
            </template>

            <button v-if="!post.deleted_at" @click="$emit('reply', post, false)" class="btn btn-sm btn-fast-reply" title="Dodaj cytat do pola odpowiedzi">
              <i class="fa fa-fw fa-quote-left"></i>
            </button>

            <button v-if="!post.deleted_at" @click="$emit('reply', post)" class="btn btn-sm" title="Odpowiedz na ten post">
              <i class="fa fa-fw fa-at"></i> <span class="d-none d-sm-inline">Odpowiedz</span>
            </button>

            <a v-if="!post.deleted_at" href="javascript:" :data-metadata="post.metadata" :data-url="post.url" class="btn btn-sm">
              <i class="fa fa-fw fa-flag"></i> <span class="d-none d-sm-inline">Zgłoś</span>
            </a>

            <div v-if="post.permissions.merge || post.permissions.adm_access" class="dropdown float-right">
              <button class="btn btn-sm" data-bs-toggle="dropdown">
                <i class="fas fa-fw fa-ellipsis-h"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-right">
                <a v-if="!post.deleted_at && post.permissions.merge && post.id !== topic.first_post_id" @click="merge" href="javascript:" class="dropdown-item">
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

    <vue-modal ref="delete-modal" @delete="deletePost(false, ...arguments)" :reasons="reasons"></vue-modal>
  </div>
</template>
<script lang="ts">
  import Vue from 'vue';
  import { Prop, Ref } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { Post, Topic, User } from '@/types/models';
  import VueClipboard from '@/plugins/clipboard';
  import VueAvatar from '../avatar.vue';
  import VueUserName from "../user-name.vue";
  import VueComment from './comment.vue';
  import VueForm from  './form.vue';
  import VueCommentForm from "./comment-form.vue";
  import VueSelect from  './../forms/select.vue';
  import VueButton from  './../forms/button.vue';
  import VueFlag from './../flags/flag.vue';
  import { mapActions, mapGetters, mapState } from "vuex";
  import VueModal from "../delete-modal.vue";
  import VueTags from '@/components/tags.vue';
  import formatDistanceToNow from 'date-fns/formatDistanceToNow';
  import pl from 'date-fns/locale/pl';
  import { default as mixins } from '../mixins/user';
  import store from "@/store";

  Vue.use(VueClipboard);

  @Component({
    name: 'post',
    mixins: [ mixins ],
    components: {
      'vue-avatar': VueAvatar,
      'vue-username': VueUserName,
      'vue-comment': VueComment,
      'vue-comment-form': VueCommentForm,
      'vue-form': VueForm,
      'vue-modal': VueModal,
      'vue-select': VueSelect,
      'vue-button': VueButton,
      'vue-flag': VueFlag,
      'vue-tags': VueTags
    },
    methods: mapActions('posts', ['vote', 'accept', 'subscribe', 'loadComments', 'loadVoters']),
    computed: {
      ...mapState('user', ['user']),
      ...mapState('topics', ['reasons']),
      ...mapGetters('user', ['isAuthorized']),
      ...mapGetters('posts', ['posts']),
      ...mapGetters('topics', ['topic'])
    }
  })
  export default class VuePost extends Vue {
    @Prop(Object)
    post!: Post;

    @Prop({default: 20})
    readonly uploadMaxSize!: number;

    @Prop()
    readonly uploadMimes!: string;

    @Ref()
    readonly form!: VueForm;

    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Ref('delete-modal')
    readonly deleteModal!: VueModal;

    isProcessing = false;
    isCollapsed = this.post.deleted_at != null;
    isCommenting = false;

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
      store.commit('posts/edit', this.post);

      if (this.post.is_editing) {
        // @ts-ignore
        this.$nextTick(() => this.form.markdown.focus());
      }
    }

    comment() {
      this.isCommenting = !this.isCommenting;

      if (this.isCommenting) {
        // @ts-ignore
        this.$nextTick(() => this.commentForm.textarea.focus());
      }
    }

    deletePost(confirm = false, reasonId: number | null = null) {
      if (confirm) {
        // @ts-ignore
        this.deleteModal.open();
      }
      else {
        // @ts-ignore
        this.deleteModal.close();
        store.dispatch('posts/delete', { post: this.post, reasonId }).then(() => this.isCollapsed = true);
      }
    }

    merge() {
      this.$confirm({
        message: 'Czy chcesz połaczyć ten post z poprzednim?',
        title: 'Połączyć posty?',
        okLabel: 'Tak, połącz'
      })
      .then(() => {
        this.$store.dispatch('posts/merge', this.post);
      });
    }

    restore() {
      this.isCollapsed = false;
      this.$store.dispatch('posts/restore', this.post);
    }

    get voters() {
      const users = this.post.voters;

      if (!users?.length) {
        return null;
      }

      return users.length > 10 ? users.splice(0, 10).concat('...').join("\n") : users.join("\n");
    }

    get tags() {
      return this.post.id === this.topic.first_post_id ? this.topic.tags : [];
    }

    get anchor() {
      return `id${this.post.id}`;
    }

    get highlight() {
      return '#' + this.anchor === window.location.hash;
    }

    get totalComments() {
      return this.post.comments_count - Object.keys(this.post.comments).length;
    }

    get flags() {
      return store.getters['flags/filter'](this.post.id, 'Coyote\\Post');
    }
  }
</script>

