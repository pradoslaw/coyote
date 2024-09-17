<template>
  <div class="card-body" :class="{'not-read': !topic.is_read, 'flagged': flag != null, 'tagged': highlight}">
    <div class="row">
      <div :class="{'col-xl-9 col-lg-10': showCategoryName, 'col-xl-10 col-lg-10': ! showCategoryName}" class="col-md-12 d-flex align-items-center">
        <a @click.left="mark" :href="getUrl()" :class="{'not-read': !topic.is_read}" class="topic-icon me-2 d-none d-md-flex">
          <i v-if="topic.is_sticky" class="fa-solid fa-thumbtack"/>
          <i v-else-if="topic.is_locked" class="fas fa-lock"/>
          <i v-else class="far fa-comments"/>
        </a>

        <div class="topic-container">
          <div class="topic-row">
            <h5 class="topic-subject text-truncate m-0">
              <a v-if="isAuthorized" @click="subscribe(topic)" href="javascript:" title="Kliknij aby wł/wył obserwowanie wątku">
                <i class="fa-solid fa-bell fa-fw on" v-if="topic.is_subscribed"/>
                <i class="fa-regular fa-bell fa-fw" v-else/>
              </a>

              <a v-if="topic.accepted_id" :href="topic.url + `?p=${topic.accepted_id}#id${topic.accepted_id}`"><i class="fas fa-check"></i></a>

              <a :href="getUrl()" :class="{'topic-unread': !topic.is_read}">{{ topic.title }}</a>
              <small v-if="showCategoryName" class="d-inline d-xl-none">
                w
                <a :href="topic.forum.url">
                  {{ topic.forum.name }}
                </a>
              </small>

              <a v-if="flag != null" :href="flag" title="Przejdź do raportowanego posta">
                <i class="fa fa-fire"></i>
              </a>
            </h5>

            <div v-if="totalPages > 1" class="d-none d-sm-inline ms-2 topic-pagination">
              <i class="far fa-file small"></i>

              <a :href="topic.url + '?page=1'">1</a>

              <template v-if="totalPages > 4">
                ...
              </template>

              <a v-if="totalPages === 4" :href="topic.url + '?page=2'">2</a>

              <template v-for="i in paginatorPages"><a :href="topic.url + '?page=' + i">{{ i }}</a>&nbsp;</template>
            </div>

            <ul class="topic-statistic list-inline small mt-1 mt-sm-0 mb-0 d-block d-sm-inline ms-sm-auto flex-sm-shrink-0">
              <li class="list-inline-item small" title="Liczba odpowiedzi">
                <i :class="{'fas topic-has-reply': topic.is_replied, 'far': !topic.is_replied}" class="fa-fw fa-comments"></i>
                {{ number(topic.replies) }}
              </li>

              <li class="list-inline-item small" title="Liczba wyświetleń">
                <i class="far fa-fw fa-eye"></i>
                {{ number(topic.views) }}
              </li>

              <li v-if="topic.score > 0" class="list-inline-item small" title="Liczba głosów oddanych na ten wątek">
                <i :class="{'fas text-primary': topic.is_voted, 'far': !topic.is_voted}" class="fa-fw fa-thumbs-up"></i>
                {{ number(topic.score) }}
              </li>
            </ul>
          </div>

          <div class="d-flex mt-1">
            <div class="d-none d-lg-inline small text-truncate">
              <a :href="topic.url + `?p=${topic.first_post_id}#id${topic.first_post_id}`"
                 class="text-muted topic-date">
                <vue-timeago :datetime="topic.created_at"></vue-timeago>
              </a>,

              <vue-username v-if="topic.user" :user="topic.user" class="mt-1 topic-username"></vue-username>
              <span v-else class="topic-username">{{ topic.user_name }}</span>
            </div>

            <ul v-if="topic.tags.length" class="tag-clouds tag-clouds-xs">
              <li v-for="tag in topic.tags"><a :href="tag.url">{{ tag.name }}</a></li>
            </ul>
          </div>
        </div>
      </div>

      <div v-if="showCategoryName" class="col-xl-1 d-none d-xl-block text-center text-truncate">
        <a :href="topic.forum.url" class="small" :title="topic.forum.name">{{ topic.forum.name }}</a>
      </div>

      <div class="col-xl-2 col-lg-2 col-md-12">
        <div class="media m-md-0">
          <a v-profile="this.topic.last_post.user ? this.topic.last_post.user.id : null">
            <vue-avatar v-bind="topic.last_post.user" class="i-35 me-2 d-none d-md-inline-block position-relative img-thumbnail"></vue-avatar>
          </a>

          <div class="media-body small text-truncate">
            <p class="mb-0 d-inline d-md-block">
              <vue-username v-if="topic.last_post.user" :user="topic.last_post.user" class="topic-username"></vue-username>
              <span class="topic-username" v-else>{{ topic.last_post.user_name }}</span>
            </p>

            <a :href="topic.url + `?p=${topic.last_post.id}#id${topic.last_post.id}`" title="Zobacz ostatni post" class="text-muted">
              <vue-timeago :datetime="topic.last_post.created_at"></vue-timeago>
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import {mapActions, mapGetters} from "vuex";
import VueAvatar from '../avatar.vue';
import {default as mixins} from '../mixins/user';
import VueUserName from '../user-name.vue';

export default {
  mixins: [mixins],
  components: {'vue-avatar': VueAvatar, 'vue-username': VueUserName},
  props: {
    topic: {
      type: Object,
      require: true,
    },
    postsPerPage: {
      type: Number,
      default: 10,
    },
    highlight: {
      type: Boolean,
    },
    showCategoryName: {
      type: Boolean,
    },
  },
  methods: {
    getUrl() {
      const urlFragment = id => `${this.topic.url}?p=${id}#id${id}`;

      // redirect straight to specific post if this field is present
      if (this.topic.user_post_id) {
        return urlFragment(this.topic.user_post_id);
      }

      // redirect to last post if topic has been read by registered user.
      return (this.topic.is_read && (this.isAuthorized && this.topic.last_post_created_at > this.$store.state.user.user.created_at) ? urlFragment(this.topic.last_post.id) : this.topic.url);
    },

    mark(event) {
      if (this.topic.is_read) {
        return;
      }

      this.$store.dispatch('topics/mark', this.topic);
      event.preventDefault();
    },

    ...mapActions('topics', ['subscribe']),
  },
  computed: {
    totalPages() {
      return Math.ceil((this.topic.replies + 1) / this.postsPerPage);
    },

    paginatorPages() {
      let pages = [];

      for (let i = Math.max(2, this.totalPages - 1); i <= this.totalPages; i++) {
        pages.push(i);
      }

      return pages;
    },

    flag() {
      const flags = this.$store.getters['flags/filter'](this.topic.id, 'Coyote\\Topic');

      return flags.length ? flags[0].url : null;
    },

    ...mapGetters('user', ['isAuthorized']),
  },
};
</script>
