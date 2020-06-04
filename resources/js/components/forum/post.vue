<template>
  <div class="card card-post">
    <div class="card-body">
      <div class="media d-lg-none">
        <div class="media-left mr-2">
          <vue-avatar v-bind="post.user" class="i-35 img-thumbnail"></vue-avatar>
        </div>

        <div class="media-body">
          <h5 class="mb-0">
            <vue-user-name v-if="post.user" :user="post.user" class="post-author"></vue-user-name>
            <span v-else>{{ post.user_name }}</span>
          </h5>

          <a :href="post.url" class="text-muted small">
            <vue-timeago :datetime="post.created_at"></vue-timeago>
          </a>
        </div>
      </div>

      <div class="row d-none d-lg-flex">
        <div class="col-2">
          <vue-user-name v-if="post.user" :user="post.user" class="post-author"></vue-user-name>
          <span v-else>{{ post.user_name }}</span>
        </div>

        <div class="col-10">
          <i class="far fa-file small"></i>

          <a :href="post.url" class="text-muted small">
            <vue-timeago :datetime="post.created_at"></vue-timeago>
          </a>
        </div>
      </div>

      <div class="row">
        <div class="d-none d-lg-block col-lg-2">
          <vue-avatar v-bind="post.user" class="post-avatar img-thumbnail"></vue-avatar>

          <ul class="post-stats list-unstyled">
            <li>
              <strong>Rejestracja:</strong>
              <small>3 lata temu</small>
            </li>

            <li>
              <strong>Ostatnio:</strong>
              <small>5 miesięcy temu</small>
            </li>

            <li>
              <strong>Postów:</strong>
              <small><a title="Znajdź posty tego użytkownika" href="http://4p.local:8880/Forum/User/78200" style="text-decoration: underline">268</a></small>
            </li>
          </ul>
        </div>

        <div class="col-12 col-lg-10">
          <div class="post-vote">
            <strong class="vote-count" title="Ocena postu">{{ post.score }}</strong>

            <a class="vote-up" href="javascript:" title="Kliknij, jeżeli post jest wartościowy (kliknij ponownie, aby cofnąć)">
              <i class="far fa-thumbs-up fa-fw"></i>
              <i class="fas fa-thumbs-up fa-fw"></i>
            </a>

            <a class="vote-accept " href="javascript:" title="Kliknij, aby ustawić tę odpowiedź jako zaakceptowaną (kliknij ponownie, aby cofnąć)">
              <i class="fas fa-check fa-fw"></i>
            </a>
          </div>

          <div class="post-content" v-html="post.html"></div>

          <div class="post-comments">
            <div class="post-comment">
              Polecam JavaStart - co prawda sam pewnie bym się nie pokusił, by zapłacić za niego, ale dzięki uprzejmości kolegi ze studiów miałem dostęp to kursów zakupionych przez niego. Bardzo fajnie przedstawiona wiedza <img class="img-smile" alt=":)" title=":)" src="/img/smilies/smile.gif"> -

              <a href="http://4p.local:8880/Profile/85242" data-user-id="85242">Belka</a>
              <a href="#comment-547369" class="text-muted small" data-timestamp="1573239142" title="08 November 2019, 19:52">08 November 2019, 19:52</a>

              <a href="http://4p.local:8880/Forum/Comment/547369" title="Edytuj ten komentarz" class="btn-comment-edit">
                <i class="fas fa-pencil-alt"></i>
              </a>

              <a href="http://4p.local:8880/Forum/Comment/Delete/547369" title="Usuń ten komentarz" class="btn-comment-del">
                <i class="fas fa-times"></i>
              </a>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="card-footer">
      <div class="row">
        <div class="d-none d-lg-block col-lg-2"></div>
        <div class="col-12 d-flex col-lg-10">
          <div v-if="!post.deleted_at">
            <button class="btn btn-sm">
              <i class="far fa-fw fa-bell"></i> <span class="d-none d-sm-inline">Obserwuj</span>
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
  import VueTimeago from '../../plugins/timeago';
  import VueAvatar from '../avatar.vue';
  import VueUserName from "../user-name.vue";

  Vue.use(VueTimeago);

  @Component({
    name: 'post',
    components: { 'vue-avatar': VueAvatar, 'vue-user-name': VueUserName },
    // mixins: [mixins]
  })
  export default class VuePost extends Vue {
    @Prop(Object)
    post!: Post;

  }
</script>
