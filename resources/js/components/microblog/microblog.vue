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

          <div class="microblog-wrapper">
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

          <div class="microblog-footer">
            <a @click="vote(microblog.id)" href="javascript:" class="btn btn-thumbs" data-toggle="tooltip" data-placement="top">
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
<!--              {% if microblog.comments_count > microblog.comments|length %}-->
<!--              <div class="show-all">-->
<!--                {% set total_comments = microblog.comments_count - microblog.comments|length %}-->
<!--                <a href="{{ route('microblog.comment.show', [microblog.id]) }}">Zobacz {{ declination(total_comments, ['pozostały', 'pozostałe', 'pozostałe'], true) }} {{ declination(total_comments, ['komentarz', 'komentarze', 'komentarzy']) }}</a>-->
<!--              </div>-->
<!--              {% endif %}-->

              <vue-comment v-for="comment in microblog.comments" :key="comment.id" :comment="comment"></vue-comment>
            </div>

<!--            {% if auth_check() %}-->
<!--            <form class="comment-form" method="POST" action="{{ route('microblog.comment.save') }}">-->
<!--              <div class="media media-darker">-->
<!--                <div class="mr-2">-->
<!--                  <a href="{{ route('profile', user('id')) }}">-->
<!--                    <img class="media-object" src="{{ user_photo(user('photo')) }}" style="width: 32px; height: 32px">-->
<!--                  </a>-->
<!--                </div>-->
<!--                <div class="media-body">-->
<!--                  <input type="hidden" name="parent_id" value="{{ microblog.id }}">-->

<!--                  <div class="write-content">-->
<!--                    <textarea name="text" placeholder="Napisz komentarz... (Ctrl+Enter aby wysłać)" class="form-control" data-prompt-url="{{ route('user.prompt') }}" rows="1"></textarea>-->
<!--                    <button type="submit" class="btn btn-sm btn-submit" title="Zapisz (Ctrl+Enter)"><i class="far fa-fw fa-share-square"></i></button>-->
<!--                  </div>-->
<!--                </div>-->
<!--              </div>-->
<!--            </form>-->
<!--            {% endif %}-->
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
  import { default as mixins } from '../mixins/user';
  import { Prop, Ref } from "vue-property-decorator";
  import { mapGetters, mapState } from "vuex";
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
      'vue-comment': VueComment
    },
    computed: {
      ...mapGetters('user', ['isAuthorized']),

    }
  })
  export default class VueMicroblog extends Vue {
    isEditing = false;

    @Ref()
    readonly confirm!: VueModal;

    //
    @Prop(Object)
    microblog!: Microblog;

    edit() {
      this.isEditing = !this.isEditing;

      if (this.isEditing) {
        // this.$nextTick(function () {
        //   this.$refs.submitText.$el.focus();
        // })
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

  }
</script>

