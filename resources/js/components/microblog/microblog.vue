<template>
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
            <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="microblog-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"></button>

            <div class="dropdown-menu dropdown-menu-right">
              <a @click="edit" class="dropdown-item btn-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
              <a @click="deleteItem" class="dropdown-item btn-remove" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
            </div>
          </div>

          <h5 class="media-heading"><vue-user-name :user="microblog.user"></vue-user-name></h5>
          <a :href="`/Mikroblogi/View/${microblog.id}#entry-${microblog.id}`" class="text-muted small"><vue-timeago :datetime="microblog.created_at"></vue-timeago></a>

          <small v-if="microblog.is_sponsored" class="text-muted" style="font-size: 11px">&bull; Sponsorowane</small>

          <div v-show="!isEditing" class="microblog-wrapper">
<!--          <div class="microblog-wrapper {{ not microblogDetailsPage ? 'microblog-wrapper-wrap' }}">-->
            <div class="microblog-text">
              <div v-html="microblog.html"></div>

              <div v-if="microblog.media" class="thumbnails row">
<!--                {% for media in microblog.media %}-->
<!--                <div class="col-6 col-md-3">-->
<!--                  <a href="{{ media.url() }}" data-toggle="lightbox" data-gallery="gallery-{{ microblog.id }}">-->
<!--                    <img class="img-thumbnail" src="{{ thumbnail(media) }}">-->
<!--                  </a>-->
<!--                </div>-->
<!--                {% endfor %}-->
              </div>


            </div>
          </div>

          <vue-form v-if="isEditing" ref="form" :microblog="microblog" class="mt-2 mb-2" @cancel="isEditing = false" @save="isEditing = false"></vue-form>

          <div class="microblog-footer">
            <a @click="vote(microblog)" href="javascript:" class="btn btn-thumbs" data-toggle="tooltip" data-placement="top">
              <i :class="{'fas text-primary': microblog.is_voted, 'far': !microblog.is_voted}" class="fa-fw fa-thumbs-up"></i>

              {{ microblog.votes }} {{ microblog.votes | declination(['głos', 'głosy', 'głosów']) }}
            </a>

            <template v-if="isAuthorized">
              <a @click="subscribe(microblog)" href="javascript:" class="btn btn-subscribe">
                <i :class="{'fas text-primary': microblog.is_subscribed, 'far': !microblog.is_subscribed}" class="fa-fw fa-bell"></i>

                Obserwuj
              </a>

              <!-- todo: klikniecie przycisku powinno ustawiac nazwer usera -->
              <a href="javascript:" class="btn btn-reply">
                <i class="far fa-fw fa-comment"></i>

                Komentuj
              </a>
            </template>
          </div>

          <div class="microblog-comments margin-sm-top">

            <div class="microblog-comments-container">
              <div v-if="microblog.comments_count > microblog.comments.length" class="show-all">
                <a @click="loadComments(microblog)" href="javascript:"><i class="far fa-comments"></i> Zobacz {{ totalComments | declination(['pozostały', 'pozostałe', 'pozostałe']) }} {{ totalComments }} {{ totalComments | declination(['komentarz', 'komentarze', 'komentarzy']) }}</a>
              </div>

              <vue-comment v-for="comment in microblog.comments" :key="comment.id" :comment="comment"></vue-comment>
            </div>

            <form v-if="isAuthorized" class="comment-form" method="POST">
              <div class="media media-darker">
                <div class="mr-2">
                  <a v-profile="user.id">
                    <vue-avatar v-bind="user" class="i-35 d-sm-block img-thumbnail"></vue-avatar>
                  </a>
                </div>
                <div class="media-body position-relative">
                  <vue-comment-form :microblog="{parent_id: microblog.id}"></vue-comment-form>
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

    <vue-modal ref="error">
      {{ error }}
    </vue-modal>
  </div>
</template>

<script lang="ts">
  import Vue from 'vue';
  import VueAvatar from '../avatar.vue';
  import VueTimeago from '../../plugins/timeago';
  import VueModal from '../modal.vue';
  import VueComment from "./comment.vue";
  import VueCommentForm from './comment-form.vue';
  import VueForm from './form.vue';
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref } from "vue-property-decorator";
  import { mapGetters, mapState, mapActions } from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import { Microblog } from "../../types/models";
  import VueUserName from "../user-name.vue";

  Vue.use(VueTimeago);

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
      'vue-comment-form': VueCommentForm
    },
    computed: {
      ...mapGetters('user', ['isAuthorized']),
      ...mapState('user', {user: state => state})
    },
    methods: mapActions('microblogs', ['loadComments', 'vote', 'subscribe'])
  })
  export default class VueMicroblog extends Vue {
    isEditing = false;
    error = '';

    @Ref()
    readonly confirm!: VueModal;

    @Ref()
    readonly form!: VueForm;

    @Prop(Object)
    microblog!: Microblog;

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        this.$nextTick(function () {
          // @ts-ignore
          this.form.$refs.textarea.$el.focus();
        })
      }
    }

    deleteItem(confirm: number) {
      if (confirm) {
        /* @ts-ignore */
        // this.confirm.open();
      } else {
        // this.confirm.close();

        store.dispatch('microblog/delete')
      }
    }

    get totalComments() {
      return this.microblog.comments_count! - this.microblog.comments.length;
    }

  }
</script>

