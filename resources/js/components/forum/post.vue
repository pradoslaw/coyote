<template>
  <div class="tree-post position-relative" :class="postIndentCssClasses">
    <vue-post-guiderail
      v-if="guiderailVisible"
      :links-to-child="linksToChild"
      :links-to-parent="linksToParent"
      :parent-levels="parentsWithSiblings"
      :expanded="postFolded"
      @toggle="guiderailToggle"
    />
    <div class="card card-post card-post-folded neon-post-folded" v-if="postFolded">
      <div class="card-body cursor-pointer p-1" @click="postUnfold">
        <div class="d-flex align-items-center">
          <span class="mx-2" v-text="postAnswersAuthorsSeeMore"/>
          <div v-for="author in postAnswersAuthors" style="width:38px;">
            <vue-avatar
              :photo="author.photo"
              :name="author.name"
              :initials="author.initials"
              class="img-thumbnail me-1"
            />
          </div>
        </div>
      </div>
    </div>
    <div v-else class="d-flex">
      <div>
        <vue-avatar
          v-if="post.user && is_mode_tree"
          :name="post.user.name"
          :photo="post.user.photo"
          :initials="post.user.initials"
          :is-online="post.user.is_online"
          class="img-thumbnail neon-post-user-avatar me-1"
          style="width:40px; z-index:1;"
        />
      </div>
      <div
        :id="anchor"
        class="card card-post neon-post flex-grow-1"
        :class="{'is-deleted': hidden, 'not-read': !post.is_read, 'highlight-flash': highlight, 'post-deleted-collapsed': isCollapsed}">
        <div v-if="post.deleted_at"
             @click="toggleDeletedPost"
             class="post-delete card-body text-decoration-none"
             :class="{'cursor-pointer':!postObscured}">
          <vue-icon name="postDeleted"/>
          Post usunięty
          <vue-timeago :datetime="post.deleted_at"/>
          <template v-if="post.deleter_name">
            {{ ' ' }}
            przez {{ post.deleter_name }}.
          </template>
          <template v-else>.</template>
          <template v-if="post.delete_reason">
            {{ ' ' }}
            Powód: {{ post.delete_reason }}.
          </template>
        </div>
        <div v-else-if="authorBlocked" class="post-delete card-body text-decoration-none" @click="isCollapsed = !isCollapsed">
          <vue-icon name="postAuthorBlocked"/>
          Treść posta została ukryta, ponieważ autorem jest zablokowany przez Ciebie użytkownik.
        </div>
        <div :class="{'collapse': isCollapsed, 'd-lg-block': !isCollapsed && is_mode_linear}" class="card-header d-none neon-post-header">
          <div class="row">
            <div class="col-2">
              <h5 class="mb-0 post-author">
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
                </div>
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
          <div class="media mb-2" :class="{'d-lg-none':is_mode_linear}">
            <div class="media-left me-2" v-if="is_mode_linear">
              <vue-avatar
                v-if="post.user"
                :name="post.user.name"
                :photo="post.user.photo"
                :is-online="post.user.is_online"
                :initials="post.user.initials"
                class="d-block i-35 img-thumbnail"
              />
            </div>

            <div class="media-body">
              <span class="mb-0 post-author me-2">
                <vue-username v-if="post.user" :user="post.user" :owner="post.user_id === topic.owner_id"/>
                <span v-else>{{ post.user_name }}</span>
              </span>
              <a :href="post.url" class="text-muted small">
                <vue-timeago :datetime="post.created_at"/>
              </a>
              <span class="ms-1" v-if="post.edit_count && is_mode_tree" :title="'edytowany ' + post.edit_count + 'x, ostatnio przez ' + post.editor.name + ', ' + editedTimeAgo">
                (edytowany)
              </span>
            </div>
          </div>

          <div class="row">
            <div class="d-none" :class="{'d-lg-block col-lg-2':is_mode_linear}" v-if="is_mode_linear">
              <template v-if="post.user">
                <vue-avatar
                  v-if="post.user"
                  :name="post.user.name"
                  :photo="post.user.photo"
                  :initials="post.user.initials"
                  :is-online="post.user.is_online"
                  class="post-avatar img-thumbnail neon-post-user-avatar"
                />
                <span v-if="post.user.group_name && !is_mode_tree" class="badge badge-secondary mb-1">
                  {{ post.user.group_name }}
                </span>
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
                         class="neon-post-counter"
                         :href="`/Forum/User/${post.user.id}`"
                         style="text-decoration: underline">{{ post.user.posts }}</a>
                    </small>
                  </li>
                </ul>
              </template>
            </div>

            <div v-show="!post.is_editing" class="col-12" :class="{'col-lg-10':is_mode_linear}">
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
                <span v-if="post.is_accepted" class="vote-accept on">
                  <vue-icon name="postAccept"/>
                </span>
              </div>
              <div class="post-content neon-post-content" :style="is_mode_tree ? {minHeight:'initial'} : {}">
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
                <template v-if="signatureVisible">
                  <hr>
                  <footer v-html="post.user.sig"/>
                </template>
              </div>
              <vue-tags :tags="tags" class="tag-clouds-md mt-2 mb-2"/>
              <div v-if="post.edit_count && is_mode_linear" class="edit-info">
                <strong>
                  edytowany {{ post.edit_count }}x, ostatnio:
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
              class="col-12 mt-2 mb-2"
              :class="{'col-lg-10':is_mode_linear}"
              :post="post"
              :show-title-input="isFirstPost"
              :show-discuss-mode-select="false"
              :show-tags-input="isFirstPost"
              :show-sticky-checkbox="isFirstPost && post.moderatorPermissions.sticky"
              :upload-mimes="uploadMimes"
              :upload-max-size="uploadMaxSize"
              @cancel="$store.commit('posts/editEnd', post)"
              @save="$store.commit('posts/editEnd', post)"
            />
          </div>
        </div>

        <div :class="{'collapse': isCollapsed}" class="card-footer neon-post-footer" v-if="!hidden && is_mode_tree && scoreDescriptionVisible">
          <div class="row">
            <div class="d-none" :class="{'d-lg-block col-lg-2':is_mode_linear}" v-if="is_mode_linear"/>
            <div class="col-12 d-flex py-1" :class="{'col-lg-10':is_mode_linear}">
              <div class="text-muted ps-2">
                {{ scoreDescription }}
              </div>
            </div>
          </div>
        </div>

        <div :class="{'collapse': isCollapsed}" class="card-footer neon-post-footer" v-if="!authorBlocked">
          <div class="row">
            <div class="d-none" :class="{'d-lg-block col-lg-2':is_mode_linear}" v-if="is_mode_linear"/>
            <div class="col-12 d-flex" :class="{'col-lg-10':is_mode_linear}">
              <div v-if="!post.deleted_at">
                <button
                  v-if="!hidden && is_mode_tree"
                  class="btn btn-sm"
                  @click="checkAuth(vote, post)"
                  :aria-label="voters"
                  data-balloon-pos="up"
                  data-balloon-break
                >
                  <span v-if="post.is_voted" class="text-primary">
                    <vue-icon name="postVoted"/>
                  </span>
                  <vue-icon name="postVote" v-else/>
                  <span v-text="post.score" class="ms-1"/>
                  <span class="d-none d-sm-inline ms-1">Doceń</span>
                </button>

                <div class="dropdown d-inline-block">
                  <button class="btn btn-sm" data-bs-toggle="dropdown">
                    <vue-icon name="postShare"/>
                    <span class="d-none d-sm-inline ms-1">Udostępnij</span>
                  </button>
                  <div class="dropdown-menu dropdown-menu-start">
                    <span v-for="item in shareDropdownItems" class="dropdown-item" @click="item.action">
                      <vue-icon :name="item.iconName"/>
                      {{ item.title }}
                    </span>
                  </div>
                </div>

                <template v-if="is_mode_linear">
                  <button v-if="!post.is_locked || post.permissions.write" @click="checkAuth(comment)" class="btn btn-sm">
                    <span v-if="isCommenting" class="text-primary">
                      <vue-icon name="postCommentActive"/>
                    </span>
                    <vue-icon v-else name="postComment"/>
                    <span class="d-none d-sm-inline ms-1">Komentuj</span>
                  </button>
                </template>

                <button class="btn btn-sm" v-if="post.permissions.accept" @click="accept(post)" title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną">
                  <template v-if="post.is_accepted">
                    <vue-icon name="postAcceptAccepted" class="text-primary"/>
                    <span class="d-none d-sm-inline ms-1">Zaakceptuj</span>
                  </template>
                  <template v-else>
                    <vue-icon name="postAccept"/>
                    <span class="d-none d-sm-inline ms-1">Zaakceptuj</span>
                  </template>
                </button>
              </div>

              <div v-if="post.permissions.write" :class="{'ms-auto':is_mode_linear}">
                <template v-if="!post.deleted_at">
                  <button @click="replyMentionAuthor" class="btn btn-sm btn-fast-reply" title="Odpowiedz na ten post" v-if="is_mode_linear">
                    <vue-icon name="postMentionAuthor"/>
                  </button>
                  <button @click="replyQuoteContent" class="btn btn-sm" title="Dodaj cytat do pola odpowiedzi" v-if="!treeTopicPostFirst">
                    <vue-icon name="postAnswerQuote"/>
                    <span class="d-none d-sm-inline ms-1">Odpowiedz</span>
                  </button>
                </template>

                <div v-if="postDropdownVisible" class="dropdown float-end">
                  <button class="btn btn-sm" data-bs-toggle="dropdown">
                    <vue-icon name="postMenuDropdown"/>
                  </button>
                  <div class="dropdown-menu dropdown-menu-end">
                    <template v-for="item in postDropdownItems">
                      <div v-if="item.divider" class="dropdown-divider"/>
                      <a v-else-if="item.link" class="dropdown-item" :href="item.link">
                        <vue-icon :name="item.iconName"/>
                        {{ item.title }}
                      </a>
                      <span
                        v-else
                        class="dropdown-item"
                        :class="{disabled: item.disabled}"
                        @click="item.action">
                        <vue-icon :name="item.iconName"/>
                        {{ item.title }}
                      </span>
                    </template>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="row" v-if="is_mode_tree && !post.deleted_at && treeTopicReplyVisible">
          <div class="d-none d-lg-block col-lg-2" v-if="is_mode_linear"/>
          <div class="col-12" :class="{'col-lg-10':is_mode_linear}">
            <div class="px-2 pb-2" :class="{'ps-lg-0 pe-lg-4 pb-lg-3':is_mode_linear}">
              <vue-form :tree-answer-post-id="post.id" :post="postDefault" @save="formSaved" ref="topicReply"/>
            </div>
          </div>
        </div>
        <vue-modal ref="delete-modal" @delete="deletePostCloseModalDelete" :reasons="reasons"/>
      </div>
    </div>
  </div>
</template>

<script lang="ts">
import axios from "axios";
import formatDistanceToNow from 'date-fns/formatDistanceToNow';
import pl from 'date-fns/locale/pl';
import {mapActions, mapGetters, mapState} from "vuex";

import declination from '../../libs/declination';
import {copyToClipboard} from '../../plugins/clipboard';
import {openFlagModal} from "../../plugins/flags";
import {confirmModal} from "../../plugins/modals";
import {formatTimeAgo, VueTimeAgo} from "../../plugins/timeago.js";
import store from "../../store/index";
import {notify} from "../../toast";
import {nextTick} from "../../vue";
import VueAvatar from '../avatar.vue';
import VueDeleteModal from "../delete-modal.vue";
import VueIcon from "../icon";
import {default as mixins} from '../mixins/user.js';
import VueTags from '../tags.vue';
import VueUserName from "../user-name.vue";
import {voteDescription} from "../voteDescription";
import VueFlag from './../flags/flag.vue';
import VueButton from './../forms/button.vue';
import VueSelect from './../forms/select.vue';
import VueCommentForm from "./comment-form.vue";
import VueComment from './comment.vue';
import VueForm from './form.vue';
import VuePostGuiderail from "./post-guiderail.vue";
import VuePostReview, {ReviewAnswer} from "./post-review.vue";

export default {
  name: 'post',
  mixins: [mixins],
  components: {
    VuePostGuiderail,
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
    post: {type: Object, required: true},
    treeItem: {type: Object, required: false},
    uploadMaxSize: {type: Number, default: 20},
    uploadMimes: {type: String},
    treeTopicPostFirst: {type: Boolean, required: false},
  },
  data() {
    return {
      isProcessing: false,
      isCollapsed: false,
      isCommenting: false,
      commentDefault: {text: '', post_id: this.post.id},
      postFolded: false,
      postDefault: {text: '', html: '', assets: []},
      treeTopicReplyVisible: false,
    };
  },
  created(): void {
    this.$data.isCollapsed = this.hidden;
    this.$data.postFolded = this.$props.post.childrenFolded;
  },
  mounted() {
    if (this.is_mode_tree && !this.post.deleted_at) {
      this.loadVoters(this.post);
    }
  },
  methods: {
    toggleDeletedPost(): void {
      if (!this.postObscured) {
        this.$data.isCollapsed = !this.$data.isCollapsed;
      }
    },
    postUnfold(): void {
      this.$data.postFolded = false;
      store.commit('posts/unfoldChildren', this.$props.post);
    },
    guiderailToggle(expanded: boolean): void {
      this.$data.postFolded = expanded;
      if (expanded) {
        store.commit('posts/foldChildren', this.$props.post);
      } else {
        store.commit('posts/unfoldChildren', this.$props.post);
      }
    },
    closePostReview(): void {
      this.post.has_review = false;
    },
    postReviewAnswer(type: ReviewAnswer): void {
      axios.post('/User/Settings/Ajax', {
        postsReviewed: {type, postId: this.post.id},
      });
    },
    ...mapActions('posts', ['vote', 'accept', 'subscribe', 'unsubscribe', 'loadComments', 'loadVoters']),
    formatDistanceToNow(date) {
      return formatDistanceToNow(new Date(date), {locale: pl});
    },
    copy(text: string, successMessage: string): void {
      if (copyToClipboard(text)) {
        notify({type: 'success', text: successMessage});
      } else {
        notify({type: 'error', text: 'Nie można skopiować linku. Sprawdź ustawienia przeglądarki.'});
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
    restore() {
      this.$data.isCollapsed = false;
      store.dispatch('posts/restore', this.post);
    },
    flagPost(): void {
      const post = this.$props.post;
      openFlagModal(post.url, post.metadata);
    },
    replyMentionAuthor(): void {
      this.$emit('reply', this.$props.post);
    },
    replyQuoteContent(): void {
      if (store.getters['topics/is_mode_tree']) {
        this.$data.treeTopicReplyVisible = !this.$data.treeTopicReplyVisible;
        if (this.$data.treeTopicReplyVisible) {
          nextTick(() => {
            this.$refs.topicReply.focus();
          });
        }
      } else {
        this.$emit('reply', this.$props.post, false);
      }
    },
    formSaved(): void {
      this.$data.treeTopicReplyVisible = false;
    },
    copyPostLink(): void {
      this.copy(this.$props.post.url, 'Link do postu znajduje się w schowku.');
    },
    copyPostLinkMarkdown(): void {
      this.copy(markdownLink(this.$props.post.id, this.$props.post.url), 'Link Markdown do postu znajduje się w schowku.');
    },
  },
  computed: {
    ...mapState('user', ['user']),
    ...mapState('topics', ['reasons']),
    ...mapGetters('user', ['isAuthorized']),
    ...mapGetters('posts', ['posts']),
    ...mapGetters('topics', ['topic', 'is_mode_tree', 'is_mode_linear']),
    postAnswersAuthors(): string {
      return store.getters['posts/postAnswersAuthors'](this.$props.post.id);
    },
    postAnswersAuthorsSeeMore(): string {
      const answers = this.postAnswersAuthors.length;
      return `Zobacz ${answers} ${declination(answers, ['pozostałą odpowiedź', 'pozostałe odpowiedzi', 'pozostałych odpowiedzi'])}.`;
    },
    editedTimeAgo() {
      return formatTimeAgo(this.$props.post.updated_at);
    },
    postObscured(): boolean {
      return this.$props.post.type === 'obscured';
    },
    postIndentCssClasses(): string[] {
      if (!this.$props.treeItem) return [];
      const level = this.$props.treeItem.nestLevel;
      if (level === 0) return ['indent', 'indent-none'];
      if (level === 1) return ['indent', 'indent-none'];
      if (level === 2) return ['indent', 'indent-1'];
      if (level === 3) return ['indent', 'indent-2'];
      if (level === 4) return ['indent', 'indent-3'];
      if (level === 5) return ['indent', 'indent-4'];
      if (level === 6) return ['indent', 'indent-5'];
      if (level === 7) return ['indent', 'indent-6'];
      if (level === 8) return ['indent', 'indent-7'];
      if (level === 9) return ['indent', 'indent-8'];
      if (level === 10) return ['indent', 'indent-9'];
      if (level === 11) return ['indent', 'indent-10'];
      if (level === 12) return ['indent', 'indent-11'];
      return ['indent', 'indent-12'];
    },
    guiderailVisible(): boolean {
      return !!this.$props.treeItem;
    },
    linksToParent(): boolean {
      return this.$props.treeItem.nestLevel > 1;
    },
    linksToChild(): boolean {
      if (this.$data.postFolded) {
        return false;
      }
      return this.$props.treeItem.hasChildren;
    },
    parentsWithSiblings(): number[] {
      return this.parentLevelsWithSiblings
        .map(parentLevel => this.$props.treeItem.nestLevel - parentLevel)
        .filter(nestLevel => nestLevel > 1)
        .map(nestLevel => this.$props.treeItem.nestLevel - nestLevel);
    },
    parentLevelsWithSiblings(): number[] {
      const parentsWithSiblings = store.getters['posts/parentLevelsWithSiblings'](this.$props.post);
      if (this.$props.treeItem.hasNextSibling) {
        parentsWithSiblings.push(0);
      }
      return parentsWithSiblings;
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
        items.push({title: 'Usuń', iconName: 'postDelete', action: this.deletePostOpenModal});
      }
      if (post.is_subscribed) {
        items.push({title: 'Przestań obserwować', iconName: 'postSubscribed', action: () => this.checkAuth(this.unsubscribe, post)});
      } else {
        items.push({title: 'Obserwuj', iconName: 'postSubscribe', action: () => this.checkAuth(this.subscribe, post)});
      }
      if (!post.deleted_at) {
        items.push({title: 'Zgłoś', iconName: 'postReport', action: this.flagPost});
      }
      const canMerge = this.is_mode_linear;
      const mod = post.moderatorPermissions;
      if (mod.delete || mod.update || mod.accept || (mod.merge && canMerge)) {
        items.push({divider: true});
      }
      if (mod.accept) {
        if (post.is_accepted) {
          items.push({title: 'Usuń akceptację jako moderator', iconName: 'postAcceptAccepted', action: () => this.accept(post)});
        } else {
          items.push({title: 'Zaakceptuj jako moderator', iconName: 'postAccept', action: () => this.accept(post)});
        }
      }
      if (mod.update) {
        items.push({title: 'Edytuj jako moderator', iconName: 'postEdit', action: this.edit, disabled: post.deleted_at || post.is_editing});
        items.push({title: 'Historia edycji', iconName: 'postEditHistoryShow', link: '/Forum/Post/Log/' + post.id});
      }
      if (mod.delete) {
        if (post.deleted_at) {
          items.push({title: 'Przywróć', iconName: 'postRestore', action: this.restore});
        } else {
          items.push({title: 'Usuń jako moderator', iconName: 'postDelete', action: this.deletePostOpenModal});
        }
      }
      if (mod.merge && canMerge) {
        items.push({title: 'Połącz z poprzednim', iconName: 'postMergeWithPrevious', action: this.merge, disabled: post.deleted_at || this.isFirstPost});
      }
      return items;
    },
    shareDropdownItems(): object[] {
      const items = [];
      items.push({title: 'Kopiuj link do postu ', iconName: 'postCopyLinkPost', action: this.copyPostLink});
      items.push({title: 'Kopiuj link do postu jako Markdown', iconName: 'postCopyLinkPost', action: this.copyPostLinkMarkdown});
      return items;
    },
    voters() {
      const users = this.post.voters;
      if (!users?.length) {
        return null;
      }
      return users.length > 10 ? users.slice(0, 10).concat('...').join("\n") : users.join("\n");
    },
    scoreDescriptionVisible(): boolean {
      return this.post.score > 1;
    },
    scoreDescription(): string {
      return voteDescription(
        this.post.score,
        this.post.is_voted,
        this.otherVoters,
      );
    },
    otherVoters(): string[] | null {
      if (this.post.voters) {
        return this.post.voters.filter((name: string) => name !== this.user.name);
      }
      return null;
    },
    tags() {
      return this.isFirstPost ? this.topic.tags : [];
    },
    anchor() {
      return `id${this.post.id}`;
    },
    highlight(): boolean {
      return this.post.highlighted;
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
    isFirstPost(): boolean {
      return this.$props.post.id === this.topic.first_post_id;
    },
    signatureVisible(): boolean {
      if (this.is_mode_tree && !this.isFirstPost) {
        return false;
      }
      return this.$props.post.user && this.$props.post.user.sig;
    },
  },
};

function markdownLink(postId: number, postUrl: string): string {
  return `[#${postId}](${postUrl})`;
}
</script>
