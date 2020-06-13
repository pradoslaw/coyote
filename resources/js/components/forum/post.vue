<template>
  <div :class="{'is-deleted': post.deleted_at}" class="card card-post">
    <div v-if="post.deleted_at" class="post-delete card-body">
      <i class="fas fa-warning"></i>

      Post usunięty
    </div>

    <div :class="{'collapse': post.deleted_at}" class="card-body">
      <div class="media d-lg-none">
        <div class="media-left mr-2">
          <vue-avatar v-if="post.user" :id="post.user.id" :name="post.user.name" :photo="post.user.photo" class="d-block i-35 img-thumbnail"></vue-avatar>
        </div>

        <div class="media-body">
          <h5 class="mb-0 post-author">
            <vue-user-name v-if="post.user" :user="post.user"></vue-user-name>
            <span v-else>{{ post.user_name }}</span>
          </h5>

          <a :href="post.url" class="text-muted small">
            <vue-timeago :datetime="post.created_at"></vue-timeago>

            <small v-if="post.ip" :title="post.ip" class="post-ip">({{ post.ip }})</small>
          </a>
        </div>
      </div>

      <div class="row d-none d-lg-flex">
        <div class="col-2">
          <h5 class="mb-0 post-author">
            <vue-user-name v-if="post.user" :user="post.user"></vue-user-name>
            <span v-else>{{ post.user_name }}</span>
          </h5>
        </div>

        <div class="col-10">
          <i class="far fa-file small"></i>

          <a :href="post.url" class="small text-body">
            <vue-timeago :datetime="post.created_at"></vue-timeago>
          </a>

          <small v-if="post.ip" :title="post.ip" class="text-muted">({{ post.ip }})</small>
        </div>
      </div>

      <div class="row">
        <div class="d-none d-lg-block col-lg-2">
          <template v-if="post.user">
            <vue-avatar v-if="post.user" :id="post.user.id" :name="post.user.name" :photo="post.user.photo" class="post-avatar img-thumbnail"></vue-avatar>

            <span v-if="post.user.group" class="badge badge-secondary mb-1">{{ post.user.group }}</span>

            <ul class="post-stats list-unstyled">
              <li>
                <strong>Rejestracja:</strong>
                <small>{{ formatDistanceToNow(post.user.created_at) }}</small>
              </li>

              <li>
                <strong>Ostatnio:</strong>
                <small>{{ formatDistanceToNow(post.user.visited_at) }}</small>
              </li>

              <li v-if="post.user.location">
                <strong>Lokalizacja:</strong>
                <small>{{ post.user.location }}</small>
              </li>

              <li v-if="post.user.allow_count">
                <strong>Postów:</strong>
                <small><a title="Znajdź posty tego użytkownika" :href="`/Forum/User/${post.user.id}`" style="text-decoration: underline">{{ post.user.posts }}</a></small>
              </li>
            </ul>
          </template>
        </div>

        <div class="col-12 col-lg-10">
          <div class="post-vote">
            <strong class="vote-count" title="Ocena postu">{{ post.score }}</strong>

            <a v-if="!post.deleted_at" :class="{'on': post.is_voted}" @click="vote(post)" class="vote-up" href="javascript:" title="Kliknij, jeżeli post jest wartościowy (kliknij ponownie, aby cofnąć)">
              <i class="far fa-thumbs-up fa-fw"></i>
              <i class="fas fa-thumbs-up fa-fw"></i>
            </a>

            <a v-if="!post.deleted_at && isAcceptAllowed" :class="{'on': post.is_accepted}" @click="accept(post)" class="vote-accept" href="javascript:" title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną (kliknij ponownie, aby cofnąć)">
              <i class="fas fa-check fa-fw"></i>
            </a>
          </div>

          <div class="post-content">
            <div v-html="post.html"></div>

            <template v-if="post.user && post.user.sig">
              <hr>
              <footer v-html="post.user.sig"></footer>
            </template>
          </div>

          <div class="post-comments">
            <vue-comment v-for="comment in post.comments" :key="comment.id" :comment="comment"></vue-comment>
          </div>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">
          <div v-if="!post.deleted_at">
            <button @click="subscribe(post)" class="btn btn-sm">
              <i :class="{'fas text-primary': post.is_subscribed, 'far': !post.is_subscribed}" class="fa-fw fa-bell"></i>

              <span class="d-none d-sm-inline">Obserwuj</span>
            </button>

            <button class="btn btn-sm">
              <i class="fas fa-fw fa-share-alt"></i> <span class="d-none d-sm-inline">Udostępnij</span>
            </button>

            <button class="btn btn-sm">
              <i class="far fa-fw fa-comment"></i> <span class="d-none d-sm-inline">Komentuj</span>
            </button>
          </div>

          <div v-if="post.permissions.write" class="ml-auto">
            <button v-if="post.permissions.update && !post.deleted_at" class="btn btn-sm">
              <i class="fa fa-fw fa-edit"></i> <span class="d-none d-sm-inline">Edytuj</span>
            </button>

            <template v-if="post.permissions.delete">
              <button v-if="!post.deleted_at" class="btn btn-sm">
                <i class="fa fa-fw fa-times"></i> <span class="d-none d-sm-inline">Usuń</span>
              </button>
              <button v-else class="btn btn-sm">
                <i class="fa fa-fw- fa-undo"></i> <span class="d-none d-sm-inline">Przywróć</span>
              </button>
            </template>

            <button v-if="!post.deleted_at" class="btn btn-sm">
              <i class="fa fa-fw fa-quote-left"></i> <span class="d-none d-sm-inline">Odpowiedz</span>
            </button>

            <button class="btn btn-sm">
              <i class="fa fa-fw fa-flag"></i> <span class="d-none d-sm-inline">Raportuj</span>
            </button>

            <div v-if="post.permissions.merge || post.permissions.adm_access" class="dropdown float-right">
              <button class="btn btn-sm" data-toggle="dropdown">
                <i class="fas fa-fw fa-ellipsis-h"></i>
              </button>

              <div class="dropdown-menu dropdown-menu-right">
                <a v-if="!post.deleted_at && post.permissions.merge" class="dropdown-item">
                  <i class="fas fa-compress fa-fw"></i> Połącz z poprzednim
                </a>

                <a v-if="post.permissions.adm_access" class="dropdown-item" :href="`/Adm/Firewall/Save?user=${post.user ? post.user.id : ''}&ip=${post.ip}`">
                  <i class="fas fa-ban fa-fw"></i> Zablokuj użytkownika
                </a>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>
<script lang="ts">
  import Vue from 'vue';
  import { Prop } from "vue-property-decorator";
  import Component from "vue-class-component";
  import { Post } from '../../types/models';

  import VueAvatar from '../avatar.vue';
  import VueUserName from "../user-name.vue";
  import VueComment from './comment.vue';
  import formatDistanceToNow from 'date-fns/formatDistanceToNow';
  import { pl } from 'date-fns/locale';
  import {mapActions, mapGetters, mapState} from "vuex";

  @Component({
    name: 'post',
    components: { 'vue-avatar': VueAvatar, 'vue-user-name': VueUserName, 'vue-comment': VueComment },
    methods: mapActions('posts', ['vote', 'accept', 'subscribe']),
    computed: {
      ...mapState('user', {user: state => state}),
      ...mapGetters('user', ['isAuthorized'])
    }
  // mixins: [mixins]
  })
  export default class VuePost extends Vue {
    @Prop(Object)
    post!: Post;

    @Prop({default: false})
    isAcceptAllowed!: boolean;

    formatDistanceToNow(date) {
      return formatDistanceToNow(new Date(date), { locale: pl });
    }

  }
</script>

