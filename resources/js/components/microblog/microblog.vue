<template>
  <!-- we use below ID in mounted() method -->
  <div :id="`entry-${microblog.id}`" class="card card-default microblog">
    <div class="card-body">
      <div v-if="microblog.deleted_at" class="alert alert-danger">
        Ten wpis został usunięty. Możesz go przywrócić jeżeli chcesz.
      </div>

      <div class="media">
        <div class="d-none d-sm-block mr-2">
          <a v-profile="microblog.user.id">
            <vue-avatar v-bind="microblog.user" :is-online="microblog.user.is_online" class="i-45 d-block img-thumbnail"></vue-avatar>
          </a>
        </div>
        <div class="media-body">
          <div class="d-flex flex-nowrap">
            <div class="flex-shrink-0 mr-auto">
              <h5 class="media-heading"><vue-username :user="microblog.user"></vue-username></h5>

              <ul class="list-inline list-inline-bullet-sm text-muted">
                <li class="list-inline-item"><a :href="microblog.url" class="text-muted small"><vue-timeago :datetime="microblog.created_at"></vue-timeago></a></li>
                <li class="list-inline-item small">{{ microblog.views }} {{ microblog.views | declination(['wyświetlenie', 'wyświetlenia', 'wyświetleń']) }}</li>
                <li v-if="microblog.is_sponsored" class="list-inline-item small">Sponsorowane</li>
              </ul>
            </div>

            <div class="microblog-tags">
              <vue-tags :tags="microblog.tags" class="tag-clouds-md"></vue-tags>
            </div>

            <div v-if="isAuthorized" class="dropdown">
              <button class="btn btn-xs border-0 text-muted" type="button" data-toggle="dropdown" aria-label="Dropdown"><i class="fa fa-ellipsis-h"></i></button>

              <div class="dropdown-menu dropdown-menu-right">
                <template v-if="microblog.permissions.update">

                  <template v-if="!microblog.deleted_at">
                    <a @click="edit(microblog)" class="dropdown-item" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
                    <a  @click="deleteItem" class="dropdown-item" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
                  </template>

                  <a v-else @click="restoreItem" class="dropdown-item" href="javascript:"><i class="fas fa-trash-restore fa-fw"></i> Przywróć</a>

                  <a v-if="microblog.permissions.moderate && !microblog.deleted_at" @click="toggleSponsored(microblog)" class="dropdown-item" href="javascript:"><i class="fas fa-dollar-sign fa-fw"></i> Sponsorowany</a>

                  <div v-if="microblog.user.id !== user.id" class="dropdown-divider"></div>
                </template>

                <a v-if="microblog.user.id !== user.id" @click="block(microblog.user)" href="javascript:" class="dropdown-item"><i class="fas fa-fw fa-ban"></i> Zablokuj użytkownika</a>
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

            <div v-if="isWrapped" @click="unwrap" class="show-more"><a href="javascript:"><i class="fa fa-arrow-alt-circle-right"></i> Zobacz całość</a></div>
          </div>

          <vue-form v-if="microblog.is_editing" ref="form" :microblog="microblog" class="mt-2 mb-2" @cancel="edit(microblog)" @save="edit(microblog)"></vue-form>

          <a @click="checkAuth(vote, microblog)" @mouseenter.once="loadVoters(microblog)" :aria-label="voters" href="javascript:" class="btn btn-thumbs" data-balloon-pos="up" data-balloon-break>
            <i :class="{'fas text-primary': microblog.is_voted, 'far': !microblog.is_voted}" class="fa-fw fa-thumbs-up"></i>

            {{ microblog.votes }} {{ microblog.votes | declination(['głos', 'głosy', 'głosów']) }}
          </a>

          <a @click="checkAuth(subscribe, microblog)" href="javascript:" class="btn btn-subscribe" title="Wł/Wył obserwowanie tego wpisu">
            <i :class="{'fas text-primary': microblog.is_subscribed, 'far': !microblog.is_subscribed}" class="fa-fw fa-bell"></i>

            <span class="d-none d-sm-inline">Obserwuj</span>
          </a>

          <a @click="checkAuth(reply, microblog.user)" href="javascript:" class="btn btn-reply" title="Odpowiedz na ten wpis">
            <i class="far fa-fw fa-comment"></i>

            <span class="d-none d-sm-inline">Komentuj</span>
          </a>

          <a @click.prevent="copy" :href="microblog.url" class="btn btn-share" title="Kopiuj link do schowka">
            <i class="fas fa-share-alt"></i>

            <span class="d-none d-sm-inline">Udostępnij</span>
          </a>

          <a v-if="isAuthorized" href="javascript:" :data-metadata="microblog.metadata" :data-url="microblog.url" class="btn btn-share" title="Zgłoś ten wpis">
            <i class="fas fa-flag"></i>

            <span class="d-none d-sm-inline">Zgłoś</span>
          </a>

          <div ref="comments" class="microblog-comments">
            <div v-if="microblog.comments_count > Object.keys(microblog.comments).length" class="show-all-comments">
              <a @click="loadComments(microblog)" href="javascript:"><i class="far fa-comments"></i> Zobacz {{ totalComments | declination(['pozostały', 'pozostałe', 'pozostałe']) }} {{ totalComments }} {{ totalComments | declination(['komentarz', 'komentarze', 'komentarzy']) }}</a>
            </div>

            <vue-comment v-for="comment in microblog.comments" :key="comment.id" :comment="comment" @reply="reply"></vue-comment>

            <form v-if="isAuthorized" method="POST">
              <div class="media bg-light rounded border-top-0">
                <div class="mr-2">
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
  import Vue from 'vue';
  import VueAvatar from '../avatar.vue';
  import VueTimeago from '../../plugins/timeago';
  import VueClipboard from '../../plugins/clipboard';
  import VueLightbox from 'vue-cool-lightbox';
  import VueComment from "./comment.vue";
  import VueCommentForm from './comment-form.vue';
  import VueForm from './form.vue';
  import VueFlag from '../flags/flag.vue';
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref, Mixins } from "vue-property-decorator";
  import { mapGetters, mapState, mapActions } from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import VueUserName from "../user-name.vue";
  import VueTags from '../tags.vue';
  import { MicroblogMixin } from "../mixins/microblog";
  import { User } from '@/types/models';
  import useBrackets from "@/libs/prompt";
  import IsImage from '@/libs/assets';

  Vue.use(VueTimeago);
  Vue.use(VueClipboard);

  @Component({
    name: 'microblog',
    mixins: [clickaway, mixins],
    store,
    components: {
      'vue-avatar': VueAvatar,
      'vue-username': VueUserName,
      'vue-comment': VueComment,
      'vue-form': VueForm,
      'vue-comment-form': VueCommentForm,
      'vue-gallery': VueLightbox,
      'vue-flag': VueFlag,
      'vue-tags': VueTags
    },
    computed: {
      ...mapGetters('user', ['isAuthorized']),
      ...mapState('user', ['user'])
    },
    methods: {
      ...mapActions('microblogs', ['vote', 'subscribe', 'loadVoters', 'loadComments', 'toggleSponsored'])
    }
  })
  export default class VueMicroblog extends Mixins(MicroblogMixin) {
    private index: number | null = null;
    private commentDefault = { parent_id: this.microblog.id, text: '' };

    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Ref('microblog-text')
    readonly microblogText!: Element;

    @Prop()
    wrap!: boolean;

    mounted() {
      if (this.wrap && this.microblogText!.clientHeight > 300) {
        this.isWrapped = true;
      }

      const pageHitHandler = () => {
        const rect = this.microblogText.getBoundingClientRect();

        if (rect.top >= 0 && rect.top <= window.innerHeight) {
          document.removeEventListener('scroll', pageHitHandler);
          store.dispatch('microblogs/hit', this.microblog);

          return true;
        }

        return false;
      }

      if (!pageHitHandler()) {
        document.addEventListener('scroll', pageHitHandler);
      }
    }

    reply(user: User) {
      this.commentForm.markdown.value += `@${useBrackets(user.name)}: `;
      this.commentForm.markdown.focus();
    }

    unwrap() {
      this.isWrapped = false;
    }

    deleteItem() {
      this.delete('microblogs/delete', this.microblog);
    }

    restoreItem() {
      store.dispatch('microblogs/restore', this.microblog);
    }

    copy() {
      if (this.$copy(this.microblog.url)) {
        this.$notify({type: 'success', text: 'Link prawidłowo skopiowany do schowka.'});
      }
      else {
        this.$notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    }

    get voters() {
      return this.microblog.voters?.length ? this.microblog.voters.join("\n") : null;
    }

    get totalComments() {
      return this.microblog.comments_count! - Object.keys(this.microblog.comments).length;
    }

    get images() {
      return this
        .microblog
        .assets
        .filter(asset => IsImage(asset.name!) && !asset.metadata)
        .map(asset => {
          return { src: asset.url, thumb: asset.thumbnail, url: asset.url };
        });
    }

    get opg() {
      return this
        .microblog
        .assets
        .find(asset => asset.metadata !== null)
    }

    get flags() {
      return store.getters['flags/filter'](this.microblog.id, 'Coyote\\Microblog');
    }
  }
</script>

