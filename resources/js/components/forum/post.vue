<template>
  <div class="card card-post card-post-folded" :class="postIndentCssClasses" v-if="postFolded">
    <div class="position-absolute post-guiderail" v-if="isChild"/>
    <div class="card-body cursor-pointer" @click="postUnfold">
      <vue-icon name="postFolded"/>
      {{ post.user.name }},
      <vue-timeago :datetime="post.created_at"/>
    </div>
  </div>
  <div v-else
       :id="anchor"
       class="card card-post"
       :class="[
         {'is-deleted': hidden, 'not-read': !post.is_read, 'highlight-flash': highlight, 'post-deleted-collapsed': isCollapsed},
         postIndentCssClasses
       ]">
    <div class="position-absolute post-guiderail" v-if="isChild">
    </div>
    <div v-if="post.deleted_at"
         @click="isCollapsed = !isCollapsed"
         class="post-delete card-body text-decoration-none">
      <vue-icon name="postDeleted"/>
      Post usunięty
      <vue-timeago :datetime="post.deleted_at"/>
      <template v-if="post.deleter_name">przez {{ post.deleter_name }}.</template>
      <template v-else>.</template>
      <template v-if="post.delete_reason">Powód: {{ post.delete_reason }}.</template>
    </div>
    <div v-else-if="authorBlocked" class="post-delete card-body text-decoration-none" @click="isCollapsed = !isCollapsed">
      <vue-icon name="postAuthorBlocked"/>
      Treść posta została ukryta, ponieważ autorem jest zablokowany przez Ciebie użytkownik.
    </div>
    <div :class="{'collapse': isCollapsed, 'd-lg-block': !isCollapsed}" class="card-header d-none">
      <div class="row">
        <div class="col-2">
          <h5 class="mb-0 post-author">
            <span @click="postFold" class="d-inline-block me-1 cursor-pointer text-muted" style="vertical-align:middle;" v-if="!authorBlocked && !post.deleted_at">
              <vue-icon name="postFold"/>
            </span>
            <vue-username v-if="post.user" :user="post.user" :owner="post.user_id === topic.owner_id"/>
            <span v-else>{{ post.user_name }}</span>
          </h5>
        </div>

        <div class="col-10 text-truncate small">
          <div class="d-flex">
            <div>
              <vue-icon v-if="post.is_read" name="postWasRead"/>
              <i v-else class="not-read" title="Nowy post"/>
              {{ ' ' }}
              <a :href="post.url" class="small text-body">
                <vue-timeago :datetime="post.created_at"/>
              </a>
              {{ ' ' }}
              <a v-if="post.ip && is_mode_linear" :href="`/Adm/Stream?ip=${post.ip}`" :title="post.ip" class="text-muted small">
                {{ post.ip }}
              </a>
              {{ ' ' }}
              <small v-if="post.browser && is_mode_linear" :title="post.browser" class="text-muted">
                {{ post.browser }}
              </small>
            </div>
            <span class="ms-auto pe-2" v-if="is_mode_tree">
              <small class="ps-2">
                Ostatnio:
                {{ ' ' }}
                <span class="text-muted">
                  {{ formatDistanceToNow(post.user.visited_at ? post.user.visited_at : post.user.created_at) }}
                </span>
              </small>
              <small class="ps-2">
                Rejestracja:
                {{ ' ' }}
                <span class="text-muted">
                  {{ formatDistanceToNow(post.user.created_at) }}
                </span>
              </small>
              <small class="ps-2" v-if="post.user.allow_count">
                Postów:
                {{ ' ' }}
                <a title="Znajdź posty tego użytkownika" :href="`/Forum/User/${post.user.id}`" style="text-decoration:underline">
                  {{ post.user.posts }}
                </a>
              </small>
            </span>
          </div>
        </div>
      </div>
    </div>
    <vue-post-review
      v-if="post.has_review"
      :post-id="post.id"
      :review-style="post.review_style"
      @close="closePostReview"
      @answer="postReviewAnswer"
    />
    <div :class="{'collapse': isCollapsed}" class="card-body">
      <div class="media d-lg-none mb-2">
        <div class="media-left me-2">
          <vue-avatar
            v-if="post.user"
            :name="post.user.name"
            :photo="post.user.photo"
            :is-online="post.user.is_online"
            :initials="post.user.initials"
            class="d-block i-35 img-thumbnail"
          ></vue-avatar>
        </div>

        <div class="media-body">
          <h5 class="mb-0 post-author">
            <vue-username v-if="post.user" :user="post.user" :owner="post.user_id === topic.owner_id"></vue-username>
            <span v-else>{{ post.user_name }}</span>
          </h5>

          <a :href="post.url" class="text-muted small">
            <vue-timeago :datetime="post.created_at"/>
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
              :name="post.user.name"
              :photo="post.user.photo"
              :initials="post.user.initials"
              :is-online="post.user.is_online"
              class="post-avatar img-thumbnail"
            ></vue-avatar>

            <span v-if="post.user.group_name" class="badge badge-secondary mb-1">{{ post.user.group_name }}</span>

            <ul class="post-stats list-unstyled">
              <li v-if="is_mode_linear">
                <strong>Rejestracja:</strong>
                <small>{{ formatDistanceToNow(post.user.created_at) }}</small>
              </li>

              <li v-if="is_mode_linear">
                <strong>Ostatnio:</strong>
                <small>
                  {{ formatDistanceToNow(post.user.visited_at ? post.user.visited_at : post.user.created_at) }}
                </small>
              </li>

              <li v-if="is_mode_linear && post.user.location">
                <strong>Lokalizacja:</strong>
                <small>{{ post.user.location }}</small>
              </li>

              <li v-if="is_mode_linear && post.user.allow_count">
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
          <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"/>
          <div class="post-vote">
            <strong
              v-if="is_mode_linear"
              class="vote-count"
              title="Ocena posta"
              @click="loadVoters(post)"
              :aria-label="voters"
              data-balloon-pos="left"
              data-balloon-break
              v-text="post.score"
            />
            <a
              v-if="!hidden && is_mode_linear"
              :class="{'on': post.is_voted}"
              :aria-label="voters"
              @click="checkAuth(vote, post)"
              @mouseenter.once="loadVoters(post)"
              data-balloon-pos="left"
              data-balloon-break
              class="vote-up"
              href="javascript:"
            >
              <vue-icon name="postVoted" v-if="post.is_voted"/>
              <vue-icon name="postVote" v-else/>
            </a>
            <template v-if="is_mode_linear">
              <a v-if="!hidden && post.permissions.accept"
                 :class="{'on': post.is_accepted}"
                 @click="accept(post)"
                 class="vote-accept"
                 href="javascript:"
                 title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną">
                <vue-icon name="postAccept"/>
              </a>
              <span v-else-if="post.is_accepted" class="vote-accept on">
                <vue-icon name="postAccept"/>
              </span>
            </template>
          </div>
          <div class="post-content">
            <div v-html="post.html"/>
            <ul v-if="post.assets.length" class="list-unstyled mb-1">
              <li v-for="asset in post.assets" class="small">
                <vue-icon name="postAssetDownload"/>
                {{ ' ' }}
                <a :href="`/assets/${asset.id}/${asset.name}`">{{ asset.name }}</a>
                {{ ' ' }}
                <small>({{ size(asset.size) }}) - <em>ściągnięć: {{ asset.count }}</em></small>
              </li>
            </ul>
            <template v-if="post.user && post.user.sig">
              <hr>
              <footer v-html="post.user.sig"/>
            </template>
          </div>
          <vue-tags :tags="tags" class="tag-clouds-md mt-2 mb-2"/>
          <div v-if="post.edit_count" class="edit-info">
            <strong>
              <a class="btn-history"
                 title="Zobacz historię zmian tego posta"
                 :href="'/Forum/Post/Log/' + post.id"
                 v-if="post.permissions.update">
                <vue-icon name="postEditHistoryShow"/>
              </a>
              edytowany {{ post.edit_count }}x,
              ostatnio:
              <vue-username :user="post.editor"/>
            </strong>
            {{ ' ' }}
            <vue-timeago :datetime="post.updated_at"/>
          </div>
          <div class="post-comments" v-if="is_mode_linear">
            <div v-if="post.comments_count > Object.keys(post.comments).length"
                 class="d-inline-block mb-2 show-all-comments">
              <a @click="loadComments(post)" href="javascript:">
                <vue-icon name="postFoldedCommentsUnfold"/>
                Zobacz
                {{ declination(totalComments, ['pozostały', 'pozostałe', 'pozostałe']) }}
                {{ totalComments }}
                {{ declination(totalComments, ['komentarz', 'komentarze', 'komentarzy']) }}
              </a>
            </div>
            <vue-comment
              v-for="comment in post.comments"
              :key="comment.id"
              :comment="comment"/>
            <vue-comment-form
              v-show="isCommenting"
              :comment="commentDefault"
              @save="isCommenting = false"
              @cancel="isCommenting = false"
              ref="comment-form"
              class="mt-2"/>
          </div>
        </div>

        <vue-form
          v-if="post.is_editing"
          ref="form"
          class="col-12 col-lg-10 mt-2 mb-2"
          :post="post"
          :show-title-input="post.id === topic.first_post_id"
          :show-discuss-mode-select="false"
          :show-tags-input="post.id === topic.first_post_id"
          :show-sticky-checkbox="post.id === topic.first_post_id && post.permissions.sticky"
          :upload-mimes="uploadMimes"
          :upload-max-size="uploadMaxSize"
          @cancel="$store.commit('posts/editEnd', post)"
          @save="$store.commit('posts/editEnd', post)"
        />
      </div>
    </div>

    <div :class="{'collapse': isCollapsed}" class="card-footer" v-if="!hidden && is_mode_tree">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"/>
        <div class="col-12 d-flex col-lg-10 py-1">
          <div class="text-muted ps-2">
            {{ scoreDescription }}
          </div>
        </div>
      </div>
    </div>

    <div :class="{'collapse': isCollapsed}" class="card-footer" v-if="!authorBlocked">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"/>
        <div class="col-12 d-flex col-lg-10">
          <div v-if="!post.deleted_at">
            <button class="btn btn-sm" v-if="!hidden && is_mode_tree" @click="checkAuth(vote, post)">
              <span v-if="post.is_voted" class="text-primary">
                <vue-icon name="postVoted"/>
              </span>
              <vue-icon name="postVote" v-else/>
              <span v-text="post.score" class="ms-1"/>
              <span class="d-none d-sm-inline ms-1">Doceń</span>
            </button>

            <button @click="checkAuth(subscribe, post)" class="btn btn-sm">
              <span v-if="post.is_subscribed" class="text-primary">
                <vue-icon name="postSubscribed"/>
              </span>
              <vue-icon v-else name="postSubscribe"/>
              <span class="d-none d-sm-inline ms-1">Obserwuj</span>
            </button>

            <button class="btn btn-sm" ref="shareButton">
              <vue-icon name="postShare"/>
              <span class="d-none d-sm-inline ms-1">Udostępnij</span>
            </button>

            <button class="btn btn-sm" v-if="is_mode_tree && post.permissions.accept" @click="accept(post)">
              <template v-if="post.is_accepted">
                <vue-icon name="postAcceptAccepted" class="text-primary"/>
                <span class="d-none d-sm-inline ms-1">Najlepsza odpowiedź</span>
              </template>
              <template v-else>
                <vue-icon name="postAccept"/>
                <span class="d-none d-sm-inline ms-1">Najlepsza odpowiedź</span>
              </template>
            </button>

            <template v-if="is_mode_linear">
              <button v-if="!post.is_locked || post.permissions.write" @click="checkAuth(comment)" class="btn btn-sm">
                <span v-if="isCommenting" class="text-primary">
                  <vue-icon name="postCommentActive"/>
                </span>
                <vue-icon v-else name="postComment"/>
                <span class="d-none d-sm-inline ms-1">Komentuj</span>
              </button>
            </template>
          </div>

          <div v-if="post.permissions.write" :class="{'ms-auto':is_mode_linear}">
            <template v-if="!post.deleted_at">
              <button @click="$emit('reply', post)" class="btn btn-sm btn-fast-reply" title="Odpowiedz na ten post" v-if="is_mode_linear">
                <vue-icon name="postMentionAuthor"/>
              </button>
              <button @click="$emit('reply', post, false)" class="btn btn-sm" title="Dodaj cytat do pola odpowiedzi">
                <vue-icon name="postAnswerQuote"/>
                <span class="d-none d-sm-inline ms-1">Odpowiedz</span>
              </button>
            </template>

            <div v-if="postDropdownVisible" class="dropdown float-end">
              <button class="btn btn-sm" data-bs-toggle="dropdown">
                <vue-icon name="postMenuDropdown"/>
              </button>
              <div class="dropdown-menu dropdown-menu-end">
                <span v-for="item in postDropdownItems"
                      class="dropdown-item"
                      :class="{disabled: item.disabled}"
                      @click="item.action">
                  <vue-icon :name="item.iconName"/>
                  {{ item.title }}
                </span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    <vue-modal ref="delete-modal" @delete="deletePostCloseModalDelete" :reasons="reasons"/>
  </div>
</template>

<script lang="ts">
import axios from "axios";
import Popover from 'bootstrap/js/dist/popover';
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import pl from 'date-fns/locale/pl';
import {mapActions, mapGetters, mapState} from "vuex";

import {copyToClipboard} from '../../plugins/clipboard';
import {openFlagModal} from "../../plugins/flags";
import {confirmModal} from "../../plugins/modals";
import {VueTimeAgo} from "../../plugins/timeago.js";
import store from "../../store/index";
import {notify} from "../../toast";
import {createVueAppPhantom, nextTick} from "../../vue";
import VueAvatar from '../avatar.vue';
import VueDeleteModal from "../delete-modal.vue";
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user.js';
import VueTags from '../tags.vue';
import VueUserName from "../user-name.vue";
import VueFlag from './../flags/flag.vue';
import VueButton from './../forms/button.vue';
import VueSelect from './../forms/select.vue';
import VueCommentForm from "./comment-form.vue";
import VueComment from './comment.vue';
import VueForm from './form.vue';
import VuePostReview, {ReviewAnswer} from "./post-review.vue";

export default {
  name: 'post',
  mixins: [mixins],
  components: {
    'vue-avatar': VueAvatar,
    'vue-button': VueButton,
    'vue-comment': VueComment,
    'vue-comment-form': VueCommentForm,
    'vue-flag': VueFlag,
    'vue-form': VueForm,
    'vue-icon': VueIcon,
    'vue-modal': VueDeleteModal,
    'vue-select': VueSelect,
    'vue-tags': VueTags,
    'vue-timeago': VueTimeAgo,
    'vue-username': VueUserName,
    'vue-post-review': VuePostReview,
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
      postFolded: false,
    };
  },
  created() {
    this.isCollapsed = this.hidden;
  },
  mounted() {
    if (this.$refs.shareButton) {
      initializeSharePopover(
        this.$refs.shareButton,
        this.post.url,
        this.post.id,
        this.post.user?.name || this.post.user_name,
        this.copy,
      );
    }
    if (this.is_mode_tree && !this.post.deleted_at) {
      this.loadVoters(this.post);
    }
  },
  methods: {
    postFold(): void {
      this.$data.postFolded = true;
    },
    postUnfold(): void {
      this.$data.postFolded = false;
    },
    closePostReview(): void {
      this.post.has_review = false;
    },
    postReviewAnswer(type: ReviewAnswer): void {
      axios.post('/User/Settings/Ajax', {
        postsReviewed: {type, postId: this.post.id},
      });
    },
    ...mapActions('posts', ['vote', 'accept', 'subscribe', 'loadComments', 'loadVoters']),
    formatDistanceToNow(date) {
      return formatDistanceToNow(new Date(date), {locale: pl});
    },
    copy(text: string): void {
      if (copyToClipboard(text)) {
        notify({type: 'success', text: 'Skopiowano link do schowka.'});
      } else {
        notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    },
    edit() {
      store.commit('posts/editStart', this.post);
      nextTick(() => (this.$refs.form as VueForm).$refs.markdown.focus());
    },
    comment() {
      this.$data.isCommenting = !this.$data.isCommenting;
      if (this.$data.isCommenting) {
        nextTick(() => (this.$refs['comment-form'] as typeof VueCommentForm).focus());
      }
    },
    deletePostOpenModal(): void {
      (this.$refs['delete-modal'] as typeof VueDeleteModal).open();
    },
    deletePostCloseModalDelete(reasonId: number): void {
      (this.$refs['delete-modal'] as typeof VueDeleteModal)!.close();
      store.dispatch('posts/delete', {post: this.post, reasonId})
        .then(() => this.$data.isCollapsed = true);
    },
    merge() {
      confirmModal({
        message: 'Czy chcesz połaczyć ten post z poprzednim?',
        title: 'Połączyć posty?',
        okLabel: 'Tak, połącz',
      }).then(() => {
        store.dispatch('posts/merge', this.post);
      });
    },
    banAuthor(): void {
      const post = this.$props.post;
      window.location.href = `/Adm/Firewall/Save?user=${post.user ? post.user.id : ''}&ip=${post.ip}`;
    },
    restore() {
      this.$data.isCollapsed = false;
      store.dispatch('posts/restore', this.post);
    },
    flagPost(): void {
      const post = this.$props.post;
      openFlagModal(post.url, post.metadata);
    },
  },
  computed: {
    ...mapState('user', ['user']),
    ...mapState('topics', ['reasons']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapGetters('posts', ['posts']),
    ...mapGetters('topics', ['topic', 'is_mode_tree', 'is_mode_linear']),
    postIndentCssClasses(): string[] {
      const post = this.$props.post;
      if (!post.indent) return [];
      if (post.indent === 1) return ['indent', 'indent-1'];
      if (post.indent === 2) return ['indent', 'indent-2'];
      if (post.indent === 3) return ['indent', 'indent-3'];
      if (post.indent === 4) return ['indent', 'indent-4'];
      return ['indent', 'indent-5'];
    },
    isChild(): boolean {
      return this.$props.post.indent > 1;
    },
    postDropdownVisible(): boolean {
      return this.postDropdownItems.length > 0;
    },
    postDropdownItems(): object[] {
      const items = [];
      const post: Post = this.$props.post;
      if (post.permissions.update) {
        items.push({title: 'Edytuj', iconName: 'postEdit', action: this.edit, disabled: post.deleted_at || post.is_editing});
      }
      if (post.permissions.delete) {
        if (post.deleted_at) {
          items.push({title: 'Przywróć', iconName: 'postRestore', action: this.restore});
        } else {
          items.push({title: 'Usuń', iconName: 'postDelete', action: this.deletePostOpenModal});
        }
      }
      if (!post.deleted_at) {
        items.push({title: 'Zgłoś', iconName: 'postReport', action: this.flagPost});
      }
      if (post.permissions.merge && this.is_mode_linear) {
        items.push({title: 'Połącz z poprzednim', iconName: 'postMergeWithPrevious', action: this.merge, disabled: post.deleted_at || post.id === topic.first_post_id});
      }
      if (post.permissions.adm_access) {
        items.push({title: 'Zbanuj użytkownika', iconName: 'postBanAuthor', action: this.banAuthor});
      }
      return items;
    },
    voters() {
      const users = this.post.voters;
      if (!users?.length) {
        return null;
      }
      return users.length > 10 ? users.slice(0, 10).concat('...').join("\n") : users.join("\n");
    },
    scoreDescription(): string {
      if (this.post.score === 0) {
        return 'Doceń post';
      }
      if (this.post.score === 1) {
        if (this.post.is_voted) {
          return 'Doceniłeś post';
        }
      }
      if (this.otherVoters) {
        if (this.post.is_voted) {
          return 'Ty i ' + this.otherVoters.join(', ') + ' doceniliście post';
        }
        if (this.otherVoters.length === 1) {
          return this.otherVoters.join(', ') + ' docenił post';
        }
        return this.otherVoters.join(', ') + ' docenili post';
      }
      return this.post.score + ' użytkowników doceniło post';
    },
    otherVoters(): string[] | undefined {
      if (this.post.voters) {
        return this.post.voters.filter((name: string) => name !== this.post.user.name);
      }
      return undefined;
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
      return this.post.user_id && store.getters['user/isBlocked'](this.post.user_id);
    },
  },
};

function initializeSharePopover(
  button: HTMLButtonElement,
  postUrl: string,
  postId: number,
  authorName: string,
  copy: (text: string) => void,
): void {
  new Popover(button, {
    container: button,
    placement: 'top',
    trigger: 'focus',
    title: 'Udostępnij',
    html: true,
    animation: false,
    content() {
      return sharePopover(postUrl, postId, authorName, copy);
    },
  });
}

function sharePopover(postUrl: string, postId: number, authorName: string, copy: (text: string) => void): Element {
  return createVueAppPhantom({
    data() {
      return {postUrl, postId, authorName};
    },
    components: {'vue-icon': VueIcon},
    template: `
      <div class="share-container">
        <div class="form-group mb-1">
          <label>Post <b>#{{ postId }}</b> od <b>{{ authorName }}</b>:</label>
          <div class="input-group">
            <input class="form-control" readonly :value="postUrl" @click="select"/>
            <div class="input-group-append">
              <button type="button" class="btn" @click="copy(postUrl)">
                <vue-icon name="postShareCopyUrl"/>
              </button>
            </div>
          </div>
        </div>
        <button type="button" class="btn btn-secondary mt-1 w-100" @click="copy(postUrl)">
          <vue-icon name="postShareCopyUrl"/>
          Skopiuj link do postu <b>{{ authorName }}</b>
        </button>
      </div>
    `,
    methods: {
      copy,
      select(event) {
        event.target.select();
      },
    },
  }, {});
}
</script>
