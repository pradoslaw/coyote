<template>
  <!-- we use below ID in mounted() method -->
  <div :id="`entry-${microblog.id}`" class="card card-default microblog">
    <div class="card-body">
      <div v-if="microblog.deleted_at" class="alert alert-danger">
        Ten wpis został usunięty. Możesz go przywrócić jeżeli chcesz.
      </div>

      <div class="media">
        <div class="d-none d-sm-block me-2">
          <a v-profile="microblog.user.id">
            <vue-avatar v-bind="microblog.user" :is-online="microblog.user.is_online" class="i-45 d-block img-thumbnail"></vue-avatar>
          </a>
        </div>
        <div class="media-body">
          <div class="d-flex flex-nowrap">
            <div class="flex-shrink-0 me-auto">
              <h5 class="media-heading">
                <vue-username :user="microblog.user"></vue-username>
              </h5>

              <ul class="list-inline mb-0 list-inline-bullet-sm microblog-statistic">
                <li class="list-inline-item">
                  <a :href="microblog.url" class="small">
                    <vue-timeago :datetime="microblog.created_at"></vue-timeago>
                  </a>
                </li>
                <li class="list-inline-item small">
                  {{ microblog.views }}
                  {{ declination(microblog.views, ['wyświetlenie', 'wyświetlenia', 'wyświetleń']) }}
                </li>
                <li v-if="microblog.is_sponsored" class="list-inline-item small">
                  Sponsorowane
                </li>
              </ul>
            </div>

            <div class="microblog-tags">
              <vue-tags :tags="microblog.tags" class="tag-clouds-md"></vue-tags>
            </div>

            <div v-if="isAuthorized" class="dropdown">
              <button class="btn btn-xs border-0 text-muted" type="button" data-bs-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis"></i></button>

              <div class="dropdown-menu dropdown-menu-end">
                <template v-if="microblog.permissions.update">

                  <template v-if="!microblog.deleted_at">
                    <a @click="edit(microblog)" class="dropdown-item" href="javascript:"><i class="fas fa-pen-to-square fa-fw"></i> Edytuj</a>
                    <a @click="deleteItem" class="dropdown-item" href="javascript:"><i class="fas fa-trash-can fa-fw"></i> Usuń</a>
                  </template>

                  <a v-else @click="restoreItem" class="dropdown-item" href="javascript:"><i class="fas fa-trash-arrow-up fa-fw"></i> Przywróć</a>

                  <a v-if="microblog.permissions.moderate && !microblog.deleted_at"
                     @click="toggleSponsored(microblog)"
                     class="dropdown-item" href="javascript:">
                    <i class="fas fa-dollar-sign fa-fw"></i>
                    Sponsorowany
                  </a>

                  <div v-if="microblog.user.id !== user.id" class="dropdown-divider"></div>
                </template>

                <a v-if="microblog.user.id !== user.id" @click="block(microblog.user)" href="javascript:" class="dropdown-item">
                  <i class="fas fa-fw fa-user-slash"></i>
                  Zablokuj użytkownika
                </a>
              </div>
            </div>
          </div>

          <div v-show="!microblog.is_editing" :class="{'microblog-wrap': isWrapped}">
            <vue-flag v-for="flag in flags" :key="flag.id" :flag="flag"></vue-flag>

            <div ref="microblog-text" v-html="microblog.html" class="microblog-text"></div>

            <a v-if="opg" :href="opg.metadata.url" :title="opg.metadata.title" class="card microblog-opg" target="_blank">
              <div :alt="opg.metadata.title" class="card-img-top" :style="`background-image: url(${opg.url})`"></div>

              <div class="card-body">
                <h5 class="card-title text-truncate">{{ opg.metadata.title }}</h5>
                <p class="card-text text-truncate">{{ opg.metadata.description }}</p>

                <small class="text-muted">{{ opg.metadata.url }}</small>
              </div>
            </a>

            <div v-if="images.length" class="row mb-2">
              <div
                v-for="(image, imageIndex) in images"
                :key="imageIndex"
                class="col-6 col-md-3"
              >
                <a @click.prevent="index = imageIndex" :href="image.url">
                  <img :alt="`Załącznik ${imageIndex}`" class="img-thumbnail" :src="image.thumb">
                </a>
              </div>
            </div>

            <div v-if="isWrapped" @click="unwrap" class="show-more"><a href="javascript:"><i class="fa fa-circle-right"></i> Zobacz całość</a></div>
          </div>

          <vue-form v-if="microblog.is_editing" ref="form" :microblog="microblog" class="mt-2 mb-2" @cancel="edit(microblog)" @save="edit(microblog)"></vue-form>

          <div class="microblog-actions">
            <a @click="checkAuth(vote, microblog)" @mouseenter.once="loadVoters(microblog)" :aria-label="voters" href="javascript:" class="btn btn-gradient" data-balloon-pos="up"
               data-balloon-break>
              <i :class="{'fas text-primary': microblog.is_voted, 'far': !microblog.is_voted}" class="fa-fw fa-thumbs-up"></i>

              {{ microblog.votes }} {{ declination(microblog.votes, ['głos', 'głosy', 'głosów']) }}
            </a>

            <a @click="checkAuth(subscribe, microblog)" href="javascript:" class="btn btn-gradient" title="Wł/Wył obserwowanie tego wpisu">
              <i :class="{'fas text-primary': microblog.is_subscribed, 'far': !microblog.is_subscribed}" class="fa-fw fa-bell"></i>

              <span class="d-none d-sm-inline">Obserwuj</span>
            </a>

            <a @click="checkAuth(reply, microblog.user)" href="javascript:" class="btn btn-gradient" title="Odpowiedz na ten wpis">
              <i class="far fa-fw fa-comment"></i>

              <span class="d-none d-sm-inline">Komentuj</span>
            </a>

            <a @click.prevent="copy" :href="microblog.url" class="btn btn-gradient" title="Kopiuj link do schowka">
              <i class="fas fa-share-nodes"></i>

              <span class="d-none d-sm-inline">Udostępnij</span>
            </a>

            <a v-if="isAuthorized" href="javascript:" :data-metadata="microblog.metadata" :data-url="microblog.url" class="btn btn-gradient" title="Zgłoś ten wpis">
              <i class="fas fa-flag"></i>

              <span class="d-none d-sm-inline">Zgłoś</span>
            </a>
          </div>
          <div ref="comments" class="microblog-comments">
            <div v-if="microblog.comments_count > Object.keys(microblog.comments).length" class="show-all-comments">
              <a @click="loadComments(microblog)" href="javascript:">
                <i class="far fa-comments"></i>
                Zobacz
                {{ declination(totalComments, ['pozostały', 'pozostałe', 'pozostałe']) }}
                {{ totalComments }}
                {{ declination(totalComments, ['komentarz', 'komentarze', 'komentarzy']) }}
              </a>
            </div>

            <vue-comment v-for="comment in microblog.comments" :key="comment.id" :comment="comment" @reply="reply"></vue-comment>

            <form v-if="isAuthorized" method="POST">
              <div class="media microblog-input rounded border-top-0">
                <div class="me-2">
                  <a v-profile="user.id">
                    <vue-avatar :photo="user.photo" :name="user.name" class="i-35 d-block img-thumbnail"></vue-avatar>
                  </a>
                </div>
                <div class="media-body position-relative">
                  <vue-comment-form :microblog="commentDefault" ref="comment-form"></vue-comment-form>
                </div>
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>

    <vue-gallery :items="images" :index="index" @close="index = null"></vue-gallery>
  </div>
</template>

<script lang="ts">
import VueLightbox from 'vue-cool-lightbox';
import {mapActions, mapGetters, mapState} from "vuex";

import IsImage from '../../libs/assets';
import useBrackets from "../../libs/prompt";
import {copyToClipboard} from '../../plugins/clipboard';
import {VueTimeAgo} from '../../plugins/timeago.js';
import store from "../../store";
import {User} from '../../types/models';
import VueAvatar from '../avatar.vue';
import VueFlag from '../flags/flag.vue';
import {MicroblogMixin} from "../mixins/microblog";
import {default as mixins} from '../mixins/user.js';
import VueTags from '../tags.vue';
import VueUserName from "../user-name.vue";

import VueCommentForm from './comment-form.vue';
import VueComment from "./comment.vue";
import VueForm from './form.vue';

export default {
  name: 'microblog',
  mixins: [mixins, MicroblogMixin],
  store,
  components: {
    'vue-avatar': VueAvatar,
    'vue-username': VueUserName,
    'vue-comment': VueComment,
    'vue-form': VueForm,
    'vue-comment-form': VueCommentForm,
    'vue-gallery': VueLightbox,
    'vue-flag': VueFlag,
    'vue-tags': VueTags,
    'vue-timeago': VueTimeAgo,
  },
  props: {
    wrap: {type: Boolean},
  },
  data() {
    return {
      index: null,
      commentDefault: {parent_id: this.microblog.id, text: '', assets: []},
    };
  },
  mounted() {
    if (this.wrap && this.$refs['microblog-text'].clientHeight > 300) {
      this.isWrapped = true;
    }

    const pageHitHandler = () => {
      const rect = this.$refs['microblog-text'].getBoundingClientRect();

      if (rect.top >= 0 && rect.top <= window.innerHeight) {
        document.removeEventListener('scroll', pageHitHandler);
        store.dispatch('microblogs/hit', this.microblog);

        return true;
      }

      return false;
    };

    if (!pageHitHandler()) {
      document.addEventListener('scroll', pageHitHandler);
    }
  },
  methods: {
    ...mapActions('microblogs', ['vote', 'subscribe', 'loadVoters', 'loadComments', 'toggleSponsored']),

    reply(user: User) {
      this.$refs['comment-form'].markdown.value += `@${useBrackets(user.name)}: `;
      this.$refs['comment-form'].markdown.focus();
    },

    unwrap() {
      this.isWrapped = false;
    },

    deleteItem() {
      this.delete('microblogs/delete', this.microblog);
    },

    restoreItem() {
      store.dispatch('microblogs/restore', this.microblog);
    },

    copy() {
      if (copyToClipboard(this.microblog.url)) {
        this.$notify({type: 'success', text: 'Link skopiowany do schowka.'});
      } else {
        this.$notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    },
  },
  computed: {
    ...mapGetters('user', ['isAuthorized']),
    ...mapState('user', ['user']),

    voters() {
      return this.splice(this.microblog.voters);
    },

    totalComments() {
      return this.microblog.comments_count! - Object.keys(this.microblog.comments).length;
    },

    images() {
      return this
        .microblog
        .assets
        .filter(asset => IsImage(asset.name!) && !asset.metadata)
        .map(asset => {
          return {src: asset.url, thumb: asset.thumbnail, url: asset.url};
        });
    },

    opg() {
      return this
        .microblog
        .assets
        .find(asset => asset.metadata !== null);
    },

    flags() {
      return store.getters['flags/filter'](this.microblog.id, 'Coyote\\Microblog');
    },
  },
};
</script>
