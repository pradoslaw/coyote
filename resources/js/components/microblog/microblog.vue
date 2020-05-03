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
            <button class="btn btn-secondary btn-xs dropdown-toggle" type="button" id="microblog-menu" data-toggle="dropdown"></button>

            <div class="dropdown-menu dropdown-menu-right">
              <a @click="edit" class="dropdown-item btn-edit" href="javascript:"><i class="fas fa-edit fa-fw"></i> Edytuj</a>
              <a @click="deleteItem" class="dropdown-item btn-remove" href="javascript:"><i class="fas fa-times fa-fw"></i> Usuń</a>
            </div>
          </div>

          <h5 class="media-heading"><vue-user-name :user="microblog.user"></vue-user-name></h5>
          <a :href="`/Mikroblogi/View/${microblog.id}#entry-${microblog.id}`" class="text-muted small"><vue-timeago :datetime="microblog.created_at"></vue-timeago></a>

          <small v-if="microblog.is_sponsored" class="text-muted small">&bull; Sponsorowane</small>

          <div v-show="!isEditing" :class="{'microblog-wrap': wrapValue}">
            <div v-html="microblog.html" class="break-word microblog-text"></div>

            <div v-if="microblog.media" class="row mt-2 mb-2">
              <div v-for="media in microblog.media" class="col-6 col-md-3">
                <a :href="media.url" data-toggle="lightbox" :data-gallery="`gallery-${microblog.id}`">
                  <img class="img-thumbnail" :src="media.url">
                </a>
              </div>
            </div>
          </div>

          <div v-if="wrapValue" @click="unwrap" class="d-block mb-3 mt-2"><a href="javascript:"><i class="fa fa-arrow-alt-circle-right"></i> Zobacz całość</a></div>

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
            <div v-if="microblog.comments_count > microblog.comments.length" class="show-all">
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
  import { Prop, PropSync, Ref } from "vue-property-decorator";
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


    @Ref()
    readonly confirm!: VueModal;

    @Ref('form')
    readonly form!: VueForm;

    @Ref('comment-form')
    readonly commentForm!: VueCommentForm;

    @Prop(Object)
    microblog!: Microblog;

    @Prop()
    wrap!: boolean;

    wrapValue = this.wrap;

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        // @ts-ignore
        this.$nextTick(() => this.form.textarea.focus());
        this.wrapValue = false;
      }
    }

    comment() {
      // @ts-ignore
      this.commentForm.textarea.value = `@${this.microblog.user.name}: `;
      // @ts-ignore
      this.commentForm.textarea.focus();
    }

    unwrap() {
      this.wrapValue = false;
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

