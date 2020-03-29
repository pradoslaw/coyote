<template>
  <div class="card-body" :class="{'not-read': !topic.is_read, 'flagged': flag != null, 'tagged': containsUserTags}">
    <div class="row">
      <div :class="{'col-xl-9 col-lg-10': showCategoryName, 'col-xl-10': ! showCategoryName}" class="col-md-12 d-flex align-items-center">
        <a @click="mark(topic)" :class="{'not-read': !topic.is_read}" class="mr-2 i-35 d-none d-md-flex position-relative align-items-center justify-content-center">
          <i v-if="topic.is_sticky" class="fas fa-info"></i>
          <i v-else-if="topic.is_locked" class="fas fa-lock"></i>
          <i v-else class="far fa-comment"></i>
        </a>

        <div class="w-100">
          <div class="row no-gutters">
            <h5 class="topic-subject text-truncate m-0">
<!--              <i class="far fa-fw fa-star" style="font-size: 12px"></i>-->
              <a v-if="topic.accepted_id" :href="topic.url + `?p=${topic.accepted_id}#id${topic.accepted_id}`"><i class="fas fa-check"></i></a>

              <a :href="getUrl()" :class="{'font-weight-bold': !topic.is_read}">{{ topic.subject }}</a>
              <small v-if="showCategoryName" class="d-inline d-xl-none"> w <a :href="topic.forum.url" class="text-body">{{ topic.forum.name }}</a></small>

              <a v-if="flag != null" :href="flag" title="Przejdź do raportowanego postu"><i class="fa fa-fire"></i></a>
            </h5>

            <div v-if="totalPages > 1" class="d-none d-sm-inline ml-2 small">
              <i class="far fa-file small"></i>

              <a :href="topic.url + '?page=1'">1</a>

              <template v-if="totalPages > 4">
              ...
              </template>

              <a v-if="totalPages === 4" :href="topic.url + '?page=2'">2</a>

              <template v-for="i in paginatorPages"><a :href="topic.url + '?page=' + i">{{ i }}</a>&nbsp;</template>
            </div>

            <ul v-if="topic.tags.length" class="tag-clouds tag-clouds-xs tag-clouds-skills ml-auto d-none d-sm-block">
              <li v-for="tag in topic.tags" ><a :href="tag.url">{{ tag.name }}</a></li>
            </ul>
          </div>

          <div class="row no-gutters mt-1">
            <div class="d-none d-lg-inline small text-truncate">
              <a :href="topic.url + `?p=${topic.first_post_id}#id${topic.first_post_id}`" class="text-muted"><vue-timeago :datetime="topic.created_at"></vue-timeago></a>,

              <a v-if="topic.user" v-profile="topic.user ? topic.user.id : null" class="mt-1 text-body" :title="topic.user.name">{{ topic.user.name }}</a>
              <span v-else>{{ topic.user_name }}</span>
            </div>

            <ul class="list-inline small text-muted mb-0 ml-lg-auto">
              <li class="list-inline-item small" title="Liczba odpowiedzi">
                <i :class="{'fas text-primary': topic.is_replied, 'far': !topic.is_replied}" class="fa-fw fa-comments"></i> {{ topic.replies | number }}
              </li>

              <li class="list-inline-item small" title="Liczba głosów oddanych na ten wątek">
                <i :class="{'fas text-primary': topic.is_voted, 'far': !topic.is_voted}" class="fa-fw fa-thumbs-up"></i> {{ topic.score | number }}
              </li>

              <li class="list-inline-item small" title="Liczba wyświetleń">
                <i class="far fa-fw fa-eye"></i> {{ topic.views | number }}
              </li>

              <li class="list-inline-item small">
                <a @click="subscribe(topic)" href="javascript:" class="text-decoration-none text-muted" title="Kliknij aby wł/wył obserwowanie wątku">
                  <i :class="{'fas text-primary': topic.is_subscribed, 'far': !topic.is_subscribed}" class="fa-fw fa-star"></i> {{ topic.subscribers | number }}
                </a>
              </li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="showCategoryName" class="col-xl-1 d-none d-xl-block text-center">
        <a :href="topic.forum.url" class="small">{{ topic.forum.name }}</a>
      </div>

      <div class="col-xl-2 col-lg-2 col-md-12 mt-1 mt-xl-0">
        <div class="media m-md-0">
          <a v-profile="this.topic.last_post.user ? this.topic.last_post.user.id : null"><vue-avatar v-bind="topic.last_post.user" class="i-35 mr-2 d-none d-md-inline-block position-relative img-thumbnail"></vue-avatar></a>

          <div class="media-body small text-truncate">
            <p class="mb-0 d-inline d-md-block">
              <a v-if="topic.last_post.user" v-profile="topic.last_post.user.id">{{ topic.last_post.user.name }}</a>
              <span v-else>{{ topic.last_post.user_name }}</span>
            </p>

            <a :href="topic.url + `?p=${topic.last_post.id}#id${topic.last_post.id}`" title="Zobacz ostatni post" class="text-muted"><vue-timeago :datetime="topic.last_post.created_at"></vue-timeago></a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
  import { default as mixins } from '../mixins/user';
  import VueTimeago from '../../plugins/timeago';
  import VueAvatar from '../avatar.vue';
  import { mixin as clickaway } from 'vue-clickaway';
  import { mapActions } from "vuex";

  Vue.use(VueTimeago);

  export default {
    mixins: [ mixins, clickaway ],
    components: { 'vue-avatar': VueAvatar },
    props: {
      topic: {
        type: Object,
        require: true
      },
      postsPerPage: {
        type: Number,
        default: 10
      },
      flag: {
        type: String
      },
      tags: {
        type: Object
      },
      showCategoryName: {
        type: Boolean
      }
    },
    methods: {
      getUrl() {
        const urlFragment = id =>  `${this.topic.url}?p=${id}#id${id}`;

        return this.topic.user_post_id
          ? urlFragment(this.topic.user_post_id)
            : (this.topic.is_read ? urlFragment(this.topic.last_post.id) : this.topic.url);
      },

      ...mapActions('topics', ['mark', 'subscribe'])
    },
    computed: {
      totalPages() {
        return Math.ceil(this.topic.replies / this.postsPerPage);
      },

      paginatorPages() {
        let pages = [];

        for (let i = Math.max(2, this.totalPages - 1); i <= this.totalPages; i++) {
          pages.push(i);
        }

        return pages;
      },

      containsUserTags() {
        if (!this.topic.tags) {
          return false;
        }

        const userTags = Object.keys(this.tags);

        return this.topic.tags.filter(tag => userTags.includes(tag.name)).length > 0;
      },
    }
  }
</script>
