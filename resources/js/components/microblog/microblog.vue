<template>
  <!-- we use below ID in mounted() method -->
  <div :id="`entry-${microblog.id}`" class="card card-default microblog">
    <div class="card-body">
      <div class="media">
        <div class="d-none d-sm-block mr-2">
          <a v-profile="microblog.user.id">
            <vue-avatar v-bind="microblog.user" class="i-45 d-block img-thumbnail"></vue-avatar>
          </a>
        </div>
        <div class="media-body">
          <div v-if="microblog.editable" class="dropdown float-right">
            <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" data-toggle="dropdown" aria-label="Dropdown"></button>

            <div class="dropdown-menu dropdown-menu-right">
              <a @click="edit" class="dropdown-item btn-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
              <a @click="deleteItem" class="dropdown-item btn-remove" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
            </div>
          </div>

          <h5 class="media-heading"><vue-user-name :user="microblog.user"></vue-user-name></h5>
          <a :href="microblog.url" class="text-muted small"><vue-timeago :datetime="microblog.created_at"></vue-timeago></a>

          <small v-if="microblog.is_sponsored" class="text-muted small">&bull; Sponsorowane</small>

          <div v-show="!isEditing" :class="{'microblog-wrap': isWrapped}">
            <div v-html="microblog.html" class="break-word microblog-text"></div>

            <div v-if="microblog.media.length" class="row mb-2">
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

          <vue-form v-if="isEditing" ref="form" :microblog="microblog" class="mt-2 mb-2" @cancel="isEditing = false" @save="isEditing = false"></vue-form>

          <a @click="checkAuth(vote, microblog)" :title="microblog.voters.join(', ')" href="javascript:" class="btn btn-thumbs">
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

    <vue-modal ref="confirm">
      Czy na pewno chcesz usunąć ten wpis?

      <template slot="buttons">
        <button @click="$refs.confirm.close()" type="button" class="btn btn-secondary" data-dismiss="modal">Anuluj
        </button>
        <button @click="deleteItem(false)" type="submit" class="btn btn-danger danger">Tak, usuń</button>
      </template>
    </vue-modal>

    <vue-gallery :items="images" :index="index" @close="index = null"></vue-gallery>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import VueAvatar from '../avatar.vue';
  import VueTimeago from '../../plugins/timeago';
  import VueClipboard from '../../plugins/clipboard';
  import VueLightbox from 'vue-cool-lightbox';
  import VueModal from '../modal.vue';
  import VueComment from "./comment.vue";
  import VueCommentForm from './comment-form.vue';
  import VueForm from './form.vue';
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref, Mixins } from "vue-property-decorator";
  import { mapGetters, mapState, mapActions } from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import VueUserName from "../user-name.vue";
  import { MicroblogMixin } from "../mixins/microblog";
  import { User } from '../../types/models';
  import useBrackets from "../../libs/prompt";

  Vue.use(VueTimeago);
  Vue.use(VueClipboard);

  @Component({
    name: 'microblog',
    mixins: [clickaway, mixins],
    store,
    components: {
      'vue-avatar': VueAvatar,
      'vue-modal': VueModal,
      'vue-user-name': VueUserName,
      'vue-comment': VueComment,
      'vue-form': VueForm,
      'vue-comment-form': VueCommentForm,
      'vue-gallery': VueLightbox
    },
    computed: {
      ...mapGetters('user', ['isAuthorized']),
      ...mapState('user', {user: state => state})
    },
    methods: mapActions('microblogs', ['vote', 'subscribe', 'loadVoters'])
  })
  export default class VueMicroblog extends Mixins(MicroblogMixin) {
    private index: number | null = null;
    private commentDefault = { parent_id: this.microblog.id, text: '' };

    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Prop()
    wrap!: boolean;

    mounted() {
      const el = document.querySelector(`#entry-${this.microblog.id} .microblog-text`);

      if (this.wrap && el!.clientHeight > 300) {
        this.isWrapped = true;
      }
    }

    reply(user: User) {
      this.commentForm.textarea.value += `@${useBrackets(user.name)}: `;
      this.commentForm.textarea.focus();
    }

    unwrap() {
      this.isWrapped = false;
    }

    deleteItem(confirm: boolean) {
      this.delete('microblogs/delete', confirm, this.microblog);
    }

    copy() {
      if (this.$copy(this.microblog.url)) {
        this.$notify({type: 'success', text: 'Link prawidłowo skopiowany do schowka.'});
      }
      else {
        this.$notify({type: 'error', text: 'Nie można skopiować linku do schowka.'});
      }
    }

    loadComments() {
      store.dispatch('microblogs/loadComments', this.microblog).then(() => {
        this.$nextTick(() => (this.$refs.comments as HTMLElement).scrollIntoView());
      });
    }

    get totalComments() {
      return this.microblog.comments_count! - Object.keys(this.microblog.comments).length;
    }

    get images() {
      return this.microblog.media.map(media => {
        return { src: media.url, thumb: media.thumbnail, url: media.url };
      })
    }
  }
</script>

