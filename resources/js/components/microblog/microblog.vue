<template>
  <div :id="`entry-${microblog.id}`" :class="{'highlight-flash': highlight}" class="card card-default microblog">
    <div class="card-body">
      <div class="media">
        <div class="d-none d-sm-block mr-2">
          <a v-profile="microblog.user.id">
            <vue-avatar v-bind="microblog.user" class="i-45 d-block img-thumbnail"></vue-avatar>
          </a>
        </div>
        <div class="media-body">
          <div v-if="microblog.editable" class="dropdown float-right">
            <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="microblog-menu" data-toggle="dropdown"></button>

            <div class="dropdown-menu dropdown-menu-right">
              <a @click="edit" class="dropdown-item btn-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
              <a @click="deleteItem" class="dropdown-item btn-remove" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
            </div>
          </div>

          <h5 class="media-heading"><vue-user-name :user="microblog.user"></vue-user-name></h5>
          <a :href="`/Mikroblogi/View/${microblog.id}#entry-${microblog.id}`" class="text-muted small"><vue-timeago :datetime="microblog.created_at"></vue-timeago></a>

          <small v-if="microblog.is_sponsored" class="text-muted small">&bull; Sponsorowane</small>

          <div v-show="!isEditing" :class="{'microblog-wrap': isWrapped}">
            <div v-html="microblog.html" class="break-word microblog-text"></div>

            <div v-if="microblog.media.length" class="row mb-2">
              <div v-for="media in microblog.media" class="col-6 col-md-3">
                <a :href="media.url" data-toggle="lightbox" :data-gallery="`gallery-${microblog.id}`">
                  <img class="img-thumbnail" :src="media.thumbnail">
                </a>
              </div>
            </div>

            <div v-if="isWrapped" @click="unwrap" class="show-more"><a href="javascript:"><i class="fa fa-arrow-alt-circle-right"></i> Zobacz całość</a></div>
          </div>

          <vue-form v-if="isEditing" ref="form" :microblog="microblog" class="mt-2 mb-2" @cancel="isEditing = false" @save="isEditing = false"></vue-form>

          <a @click="vote(microblog)" href="javascript:" class="btn btn-thumbs" data-toggle="tooltip" data-placement="top">
            <i :class="{'fas text-primary': microblog.is_voted, 'far': !microblog.is_voted}" class="fa-fw fa-thumbs-up"></i>

            {{ microblog.votes }} {{ microblog.votes | declination(['głos', 'głosy', 'głosów']) }}
          </a>

          <template v-if="isAuthorized">
            <a @click="subscribe(microblog)" href="javascript:" class="btn btn-subscribe">
              <i :class="{'fas text-primary': microblog.is_subscribed, 'far': !microblog.is_subscribed}" class="fa-fw fa-bell"></i>

              Obserwuj
            </a>

            <a @click="comment" href="javascript:" class="btn btn-reply">
              <i class="far fa-fw fa-comment"></i>

              Komentuj
            </a>
          </template>

          <div class="microblog-comments">
            <div v-if="microblog.comments_count > Object.keys(microblog.comments).length" class="show-all">
              <a @click="loadComments(microblog)" href="javascript:"><i class="far fa-comments"></i> Zobacz {{ totalComments | declination(['pozostały', 'pozostałe', 'pozostałe']) }} {{ totalComments }} {{ totalComments | declination(['komentarz', 'komentarze', 'komentarzy']) }}</a>
            </div>

            <vue-comment v-for="comment in microblog.comments" :key="comment.id" :comment="comment"></vue-comment>

            <form v-if="isAuthorized" method="POST">
              <div class="media bg-light rounded border-top-0">
                <div class="mr-2">
                  <a v-profile="user.id">
                    <vue-avatar v-bind="user" class="i-35 d-sm-block img-thumbnail"></vue-avatar>
                  </a>
                </div>
                <div class="media-body position-relative">
                  <vue-comment-form :microblog="{parent_id: microblog.id}" ref="comment-form"></vue-comment-form>
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
  import { Prop, Ref, Mixins } from "vue-property-decorator";
  import { mapGetters, mapState, mapActions } from "vuex";
  import Component from "vue-class-component";
  import { mixin as clickaway } from "vue-clickaway";
  import store from "../../store";
  import VueUserName from "../user-name.vue";
  import { MicroblogMixin } from "../mixins/microblog";

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
  export default class VueMicroblog extends Mixins(MicroblogMixin) {
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

    comment() {
      // @ts-ignore
      this.commentForm.textarea.value = `@${this.microblog.user.name}: `;
      // @ts-ignore
      this.commentForm.textarea.focus();
    }

    unwrap() {
      this.isWrapped = false;
    }

    deleteItem(confirm: boolean) {
      this.delete('microblogs/delete', confirm, this.microblog);
    }

    get totalComments() {
      return this.microblog.comments_count! - Object.keys(this.microblog.comments).length;
    }

    get anchor() {
      return `entry-${this.microblog.id}`;
    }

    get highlight() {
      return '#' + this.anchor === window.location.hash;
    }
  }
</script>

