<template>
  <div class="card-body">
    <div class="row">
      <div class="col-lg-9 col-md-12 d-flex align-items-center">
        <i v-if="isLocked" class="fas fa-lock float-left d-none d-sm-flex img-thumbnail align-items-center justify-content-center icon mr-2 position-relative not-read" :style="{width: '35px', height: '35px'}">
          <b v-if="isRead"></b>
        </i>
        <vue-avatar v-else v-bind="user" class="not-read">
<!--          <b v-if="isRead"></b>-->
        </vue-avatar>

        <div class="w-100">
          <h5 class="topic-subject text-truncate m-0"><a :href="url">{{ subject }}</a></h5>

          <div class="row no-gutters">
            <div class="d-none d-lg-inline mt-1 mr-3 small">
              <span class="text-muted"><vue-timeago :datetime="createdAt"></vue-timeago></span>,

              <a v-if="user" v-profile="user.id" class="mt-1" style="color: #555">{{ user.name }}</a>
              <span v-else>{{ userName }}</span>
            </div>

            <ul class="list-inline small text-muted mt-1 mb-0">
              <li class="list-inline-item small">
                <i class="fas fa-fw fa-thumbs-up"></i> {{ score | number }} <span class="d-none d-lg-inline">głosów</span>
              </li>

              <li class="list-inline-item small">
                <i class="fas fa-fw fa-comments"></i> {{ replies | number }} <span class="d-none d-lg-inline">odpowiedzi</span>
              </li>

              <li class="list-inline-item small">
                <i class="far fa-fw fa-eye"></i> {{ views | number }} <span class="d-none d-lg-inline">wyświetleń</span>
              </li>

              <li class="list-inline-item small">
                <i class="far fa-fw fa-star"></i> <span class="d-none d-lg-inline">0 obserwuje</span>
              </li>
            </ul>

            <ul v-if="tags.length" class="tag-clouds tag-clouds-xs tag-clouds-skills ml-auto d-none d-sm-block">
              <li v-for="tag in tags" ><a :href="tag.url">{{ tag.name }}</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div class="col-lg-3 col-md-12 mt-1 mt-sm-2 mt-lg-0">
        <div class="media m-md-0">
          <vue-avatar v-bind="lastPost.user"></vue-avatar>

          <div class="media-body small text-truncate">
            <p class="text-truncate mb-1 d-none d-sm-block small">
              {{ lastPost.excerpt }}
            </p>

            <span class="text-muted"><vue-timeago :datetime="lastPost.created_at"></vue-timeago></span>,

            <a v-if="lastPost.user" v-profile="lastPost.user.id">{{ lastPost.user.name }}</a>
            <span v-else>{{ lastPost.user_name }}</span>
          </div>
        </div>

<!--        <a title="Obserwuj wątek" href="#" class="btn-watch-sm">-->
<!--          <i class="far fa-star"></i>-->
<!--        </a>-->
      </div>
    </div>
  </div>
</template>

<script>
  import { default as mixins } from '../mixins/user';
  import VueTimeago from '../../plugins/timeago';
  import VueAvatar from '../avatar.vue';
  import { mixin as clickaway } from 'vue-clickaway';
  import store from '../../store';
  import { mapGetters, mapActions } from "vuex";

  Vue.use(VueTimeago);

  export default {
    mixins: [ mixins, clickaway ],
    components: { 'vue-avatar': VueAvatar },
    props: {
      url: {
        type: String,
        required: true
      },
      subject: {
        type: String,
        required: true
      },
      isLocked: {
        type: Boolean,
        default: false
      },
      isRead: {
        type: Boolean
      },
      score: {
        type: Number
      },
      views: {
        type: Number
      },
      replies: {
        type: Number
      },
      tags: {
        type: Array
      },
      createdAt: {
        type: String,
        required: true
      },
      user: {
        type: Object
      },
      userName: {
        type: String
      },
      lastPost: {
        type: Object,
        required: true
      }
    }
  }
</script>
