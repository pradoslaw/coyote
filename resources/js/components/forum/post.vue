<template>
  <div :id="anchor"
       class="card card-post"
       :class="{'is-deleted': hidden, 'not-read': !post.is_read, 'highlight-flash': highlight, 'post-deleted-collapsed':isCollapsed}">
    <div v-if="post.deleted_at"
         @click="isCollapsed = !isCollapsed"
         class="post-delete card-body text-decoration-none">
      <i class="fa-solid fa-trash-can fa-fw"/>
      Post usunięty
      <vue-timeago :datetime="post.deleted_at"/>
      <template v-if="post.deleter_name">przez {{ post.deleter_name }}.</template>
      <template v-else>.</template>
      <template v-if="post.delete_reason">Powód: {{ post.delete_reason }}.</template>
    </div>
    <div v-else-if="authorBlocked" class="post-delete card-body text-decoration-none" @click="isCollapsed = !isCollapsed">
      <i class="fa-fw fa-solid fa-user-slash"/>
      Treść posta została ukryta, ponieważ autorem jest zablokowany przez Ciebie użytkownik.
    </div>
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

          <a v-if="post.ip" :href="`/Adm/Stream?ip=${post.ip}`" :title="post.ip" class="text-muted small">
            {{ post.ip }}
          </a>
          <small v-if="post.browser" :title="post.browser" class="text-muted">{{ post.browser }}</small>
        </div>
      </div>
    </div>

    <div :class="{'collapse': isCollapsed}" class="card-body">
      <div class="media d-lg-none mb-2">
        <div class="media-left me-2">
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
                <small>
                  {{ formatDistanceToNow(post.user.visited_at ? post.user.visited_at : post.user.created_at) }}
                </small>
              </li>

              <li v-if="post.user.location">
                <strong>Lokalizacja:</strong>
                <small>{{ post.user.location }}</small>
              </li>

              <li v-if="post.user.allow_count">
                <strong>Postów:</strong>
                <small>
                  <a title="Znajdź posty tego użytkownika"
                     :href="`/Forum/User/${post.user.id}`"
                     style="text-decoration: underline">{{ post.user.posts }}</a>
                </small>
              </li>
            </ul>
          </template>
        </div>

        <div v-show="!post.is_editing" class="col-12 col-lg-10">
          <vue-flag v-for="flag in flags" :key="flag.id" :flag.sync="flag"></vue-flag>

          <div class="post-vote">
            <strong class="vote-count" title="Ocena posta">{{ post.score }}</strong>

            <a
              v-if="!hidden"
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

            <a v-if="!hidden && post.permissions.accept"
               :class="{'on': post.is_accepted}"
               @click="accept(post)"
               class="vote-accept"
               href="javascript:"
               title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną (kliknij ponownie, aby cofnąć)">
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
                <small>({{ size(asset.size) }}) - <em>ściągnięć: {{ asset.count }}</em></small>
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
              <a class="btn-history"
                 :title="post.permissions.update ? 'Zobacz historię zmian tego posta' : ''"
                 :href="post.permissions.update ? `/Forum/Post/Log/${post.id}` : ''">
                <i class="fas fa-up-right-from-square"></i>
              </a>
              edytowany {{ post.edit_count }}x,
              ostatnio:
              <vue-username :user="post.editor"></vue-username>
            </strong>
            <vue-timeago :datetime="post.updated_at"></vue-timeago>
          </div>

          <div class="post-comments">
            <div v-if="post.comments_count > Object.keys(post.comments).length"
                 class="d-inline-block mb-2 show-all-comments">
              <a @click="loadComments(post)" href="javascript:">
                <i class="far fa-comments"></i>
                Zobacz
                {{ declination(totalComments, ['pozostały', 'pozostałe', 'pozostałe']) }}
                {{ totalComments }}
                {{ declination(totalComments, ['komentarz', 'komentarze', 'komentarzy']) }}
              </a>
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

    <div :class="{'collapse': isCollapsed}" class="card-footer" v-if="!authorBlocked">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">
          <div v-if="!post.deleted_at">
            <button @click="checkAuth(subscribe, post)" class="btn btn-sm">
              <i :class="{'fas text-primary': post.is_subscribed, 'far': !post.is_subscribed}"
                 class="fa-fw fa-bell"></i>

              <span class="d-none d-sm-inline">Obserwuj</span>
            </button>

            <button class="btn btn-sm" ref="shareButton">
              <i class="fas fa-fw fa-share-nodes"/>
              <span class="d-none d-sm-inline">Udostępnij</span>
            </button>

            <button v-if="!post.is_locked || post.permissions.write" @click="checkAuth(comment)" class="btn btn-sm">
              <i :class="{'fas text-primary': isCommenting, 'far': !isCommenting}" class="fa-fw fa-comment"></i>
              <span class="d-none d-sm-inline">Komentuj</span>
            </button>
          </div>

          <div v-if="post.permissions.write" class="ms-auto">
            <button v-if="post.permissions.update && !post.deleted_at" @click="edit" class="btn btn-sm">
              <i :class="{'text-primary': post.is_editing}" class="fas fa-fw fa-pen-to-square"></i>
              <span class="d-none d-sm-inline">Edytuj</span>
            </button>

            <template v-if="post.permissions.delete">
              <button v-if="!post.deleted_at" @click="deletePost(true)" class="btn btn-sm">
                <i class="fa fa-fw fa-trash-can"></i> <span class="d-none d-sm-inline">Usuń</span>
              </button>
              <button v-else class="btn btn-sm" @click="restore">
                <i class="fa fa-fw fa-arrow-rotate-left"></i> <span class="d-none d-sm-inline">Przywróć</span>
              </button>
            </template>

            <template v-if="!post.deleted_at">
              <button @click="$emit('reply', post)" class="btn btn-sm btn-fast-reply" title="Odpowiedz na ten post">
                <i class="fa fa-fw fa-at"></i>
              </button>

              <button @click="$emit('reply', post, false)" class="btn btn-sm" title="Dodaj cytat do pola odpowiedzi">
                <i class="fa fa-fw fa-quote-left"></i> <span class="d-none d-sm-inline">Odpowiedz</span>
              </button>

              <a href="javascript:" :data-metadata="post.metadata" :data-url="post.url" class="btn btn-sm">
                <i class="fa fa-fw fa-flag"></i> <span class="d-none d-sm-inline">Zgłoś</span>
              </a>
            </template>

            <div v-if="post.permissions.merge || post.permissions.adm_access" class="dropdown float-end">
              <button class="btn btn-sm" data-bs-toggle="dropdown">
                <i class="fas fa-fw fa-ellipsis"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-end">
                <a v-if="!post.deleted_at && post.permissions.merge && post.id !== topic.first_post_id"
                   @click="merge"
                   href="javascript:" class="dropdown-item">
                  <i class="fas fa-compress fa-fw"></i>
                  Połącz z poprzednim
                </a>

                <a v-if="post.permissions.adm_access"
                   class="dropdown-item"
                   :href="`/Adm/Firewall/Save?user=${post.user ? post.user.id : ''}&ip=${post.ip}`">
                  <i class="fas fa-user-slash fa-fw"></i>
                  Zbanuj użytkownika
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
import Popover from 'bootstrap/js/dist/popover';
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import pl from 'date-fns/locale/pl';
import Vue from 'vue';
import {mapActions, mapGetters, mapState} from "vuex";

import VueClipboard from '../../plugins/clipboard';
import store from "../../store/index";
import VueAvatar from '../avatar.vue';
import VueDeleteModal from "../delete-modal.vue";
import {default as mixins} from '../mixins/user.js';
import VueTags from '../tags.vue';
import VueUserName from "../user-name.vue";
import VueFlag from './../flags/flag.vue';
import VueButton from './../forms/button.vue';
import VueSelect from './../forms/select.vue';
import VueCommentForm from "./comment-form.vue";
import VueComment from './comment.vue';
import VueForm from './form.vue';

Vue.use(VueClipboard);

export default Vue.extend({
  name: 'post',
  mixins: [mixins],
  components: {
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-comment': VueComment,
    'vue-comment-form': VueCommentForm,
    'vue-form': VueForm,
    'vue-modal': VueDeleteModal,
    'vue-select': VueSelect,
    'vue-button': VueButton,
    'vue-flag': VueFlag,
    'vue-tags': VueTags,
  },
  props: {
    post: {
      type: Object,
      required: true,
    },
    uploadMaxSize: {
      type: Number,
      default: 20,
    },
    uploadMimes: {
      type: String,
    },
  },
  data() {
    return {
      isProcessing: false,
      isCollapsed: false,
      isCommenting: false,
      commentDefault: {text: '', post_id: this.post.id},
    };
  },
  created() {
    this.isCollapsed = this.hidden;
  },
  mounted() {
    initializeSharePopover(
      this.$refs.shareButton,
      baseUrl(this.post.url),
      this.post.url,
      this.post.user.name,
      this.copy,
    );
  },
  methods: {
    ...mapActions('posts', ['vote', 'accept', 'subscribe', 'loadComments', 'loadVoters']),
    formatDistanceToNow(date) {
      return formatDistanceToNow(new Date(date), {locale: pl});
    },
    copy(text: string): void {
      if (this.$copy(text)) {
        this.$notify({type: 'success', text: 'Skopiowano link do schowka.'});
      } else {
        this.$notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    },
    edit() {
      store.commit('posts/edit', this.post);
      if (this.post.is_editing) {
        this.$nextTick(() => (this.$refs.form as VueForm).markdown.focus());
      }
    },
    comment() {
      this.$data.isCommenting = !this.$data.isCommenting;
      if (this.$data.isCommenting) {
        this.$nextTick(() => (this.$refs['comment-form'] as typeof VueCommentForm).focus());
      }
    },
    deletePost(confirm = false, reasonId: number | null = null) {
      if (confirm) {
        (this.$refs['delete-modal'] as typeof VueDeleteModal).open();
      } else {
        (this.$refs['delete-modal'] as typeof VueDeleteModal)!.close();
        store.dispatch('posts/delete', {post: this.post, reasonId}).then(() => this.$data.isCollapsed = true);
      }
    },
    merge() {
      this.$confirm({
        message: 'Czy chcesz połaczyć ten post z poprzednim?',
        title: 'Połączyć posty?',
        okLabel: 'Tak, połącz',
      }).then(() => {
        this.$store.dispatch('posts/merge', this.post);
      });
    },
    restore() {
      this.$data.isCollapsed = false;
      this.$store.dispatch('posts/restore', this.post);
    },
  },
  computed: {
    ...mapState('user', ['user']),
    ...mapState('topics', ['reasons']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapGetters('posts', ['posts']),
    ...mapGetters('topics', ['topic']),
    voters() {
      const users = this.post.voters;
      if (!users?.length) {
        return null;
      }
      return users.length > 10 ? users.splice(0, 10).concat('...').join("\n") : users.join("\n");
    },
    tags() {
      return this.post.id === this.topic.first_post_id ? this.topic.tags : [];
    },
    anchor() {
      return `id${this.post.id}`;
    },
    highlight() {
      return '#' + this.anchor === window.location.hash;
    },
    totalComments() {
      return this.post.comments_count - Object.keys(this.post.comments).length;
    },
    flags() {
      return store.getters['flags/filter'](this.post.id, 'Coyote\\Post');
    },
    hidden(): boolean {
      return this.post.deleted_at || this.authorBlocked;
    },
    authorBlocked(): boolean {
      return this.post.user_id && this.$store.getters['user/isBlocked'](this.post.user_id);
    },
  },
});

function initializeSharePopover(
  button: HTMLButtonElement,
  threadUrl: string,
  postUrl: string,
  authorName: string,
  copy: (text: string) => void,
): void {
  new Popover(button, {
    container: button,
    placement: 'top',
    trigger: 'focus',
    title: 'Udostępnij',
    html: true,
    content() {
      return sharePopover(threadUrl, postUrl, authorName, copy);
    },
  });
}

function sharePopover(threadUrl: string, postUrl: string, authorName: string, copy: (text: string) => void): Element {
  const input = {
    props: ['value'],
    template: `
      <div class="input-group">
        <input class="form-control" readonly :value="value" @click="select"/>
        <div class="input-group-append">
          <button type="button" class="btn" @click="$emit('copy', value)">
            <i class="fa-solid fa-copy"/>
          </button>
        </div>
      </div>
    `,
    methods: {
      select(event) {
        event.target.select();
      },
    },
  };
  return vueElement({
    data() {
      return {threadUrl, postUrl, authorName};
    },
    components: {'vue-input': input},
    template: `
      <div class="share-container">
        <div class="form-group mb-2">
          <label>Odnośnik do wątku:</label>
          <vue-input :value="threadUrl" @copy="copy"/>
        </div>
        <div class="form-group mb-2">
          <label>Odnośnik do postu <b>{{ authorName }}</b>:</label>
          <vue-input :value="postUrl" @copy="copy"/>
        </div>
      </div>
    `,
    methods: {copy},
  });

  function vueElement(component): Element {
    return new Vue(component).$mount().$el;
  }
}

function baseUrl(longUrl) {
  const url = new URL(longUrl);
  return url.origin + url.pathname;
}
</script>
