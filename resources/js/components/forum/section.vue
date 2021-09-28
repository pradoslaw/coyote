<template>
  <div class="card-section card pt-1">
    <div class="section-name pb-2 pl-lg-3 pt-lg-2 pr-lg-2">
      <h2 class="float-left">
        <a v-if="$listeners.collapse" href="javascript:" @click="collapse"><i :class="[isCollapse ? 'fa-plus-square': 'fa-minus-square']" class="far"></i> {{ name }}</a>
        <template v-else>{{ name }}</template>
      </h2>

      <div v-if="isAuthorized && !categories[0].parent_id" :class="{'open': isDropdown}" v-on-clickaway="hideDropdown" class="dropdown float-right dropleft">
        <a href="javascript:" @click="isDropdown = ! isDropdown" class="card-cog mt-2 mr-2"><i class="fas fa-cogs"></i></a>

        <div :class="{'d-block': isDropdown}" class="dropdown-menu">
          <a v-for="category in categories" href="javascript:" class="dropdown-item" @click="toggle(category)">
            <i :class="{'fa-check': !category.is_hidden}" class="fa"></i>

            {{ category.name }}
          </a>
        </div>
      </div>

      <div class="clearfix"></div>
    </div>

    <section v-if="!isCollapse" class="card card-default card-categories mb-0">
      <div v-for="(category, index) in categories" v-if="!category.is_hidden" :class="{'not-read': !category.is_read}" class="card-body">
        <div class="row">
          <div class="col-6 col-md-12 col-lg-5 d-flex align-items-center">
            <a @click="mark(category)" :class="{'not-read': !category.is_read}" class="d-none d-lg-block position-relative mr-2">
              <i v-if="category.is_locked" class="logo fas fa-lock "></i>

              <i v-else :class="[className(category.name)]" class="logo far fa-comments"></i>
            </a>

            <div class="overflow-hidden">
              <h3><a :href="category.url">{{ category.name }}</a></h3>

              <vue-tags v-if="category.enable_tags && !category.children" :tags="category.tags" class="tag-clouds-sm"></vue-tags>

              <ul v-if="category.children" class="list-inline list-sub d-md-block d-lg-block">
                <li v-for="children in category.children" class="list-inline-item">
                  <i v-if="children.is_read" class="far fa-file"></i>
                  <i v-else class="not-read" title="Nowe posty w tej kategorii"></i>

                  <a :href="children.url">{{ children.name }}</a>
                </li>
              </ul>
            </div>
          </div>

          <div v-if="!category.is_redirected" class="col-6 col-md-12 col-lg-2 d-flex align-items-center">
            <ul class="list-inline mb-0 mt-1">
              <li class="list-inline-item">
                <strong>{{ category.topics | number }}</strong>
                <small class="text-muted text-wide-spacing">{{ category.topics | declination(['wątek', 'wątków', 'wątków']) }}</small>
              </li>

              <li class="list-inline-item">
                <strong>{{ category.posts | number }}</strong>
                <small class="text-muted text-wide-spacing">{{ category.posts | declination(['post', 'postów', 'postów']) }}</small>
              </li>
            </ul>
          </div>

          <div v-else class="col-12 col-lg-7 text-center">
            Liczba przekierowań: {{ category.redirects }}
          </div>

          <div v-if="!category.is_redirected" class="col-12 col-lg-5">
            <div v-if="category.post" class="media">
              <a v-profile="category.post.user ? category.post.user.id : null">
                <vue-avatar v-bind="category.post.user" class="i-38 mr-2 d-none d-sm-block img-thumbnail"></vue-avatar>
              </a>

              <div class="media-body overflow-hidden">
                <p class="text-truncate mb-1">
                  <a :href="getUrl(category)">{{ category.topic.title }}</a>
                </p>

                <span class="text-muted"><vue-timeago :datetime="category.post.created_at"></vue-timeago></span>,

                <vue-username v-if="category.post.user" :user="category.post.user"></vue-username>
                <span v-else>{{ category.post.user_name }}</span>

                <div class="toolbox">
                  <a href="javascript:" title="Oznacz jako przeczytane" @click="mark(category)"><i class="far fa-eye"></i></a>

                  <a v-if="isAuthorized" :class="{'disabled': isBeginning(index)}" title="Przesuń w górę" @click="up(category)"><i class="fas fa-caret-up"></i></a>
                  <a v-if="isAuthorized" :class="{'disabled': isEnding(index)}" title="Przesuń w dół" @click="down(category)"><i class="fas fa-caret-down"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
</template>

<script>
import {default as mixins} from '../mixins/user';
import {mixin as clickaway} from 'vue-clickaway';
import store from '../../store';
import {mapGetters, mapActions} from "vuex";
import VueAvatar from '../avatar.vue';
import VueUsername from '../user-name.vue';
import VueTags from '@/components/tags.vue';

export default {
  mixins: [mixins, clickaway],
  components: {'vue-avatar': VueAvatar, 'vue-username': VueUsername, 'vue-tags': VueTags},
  store,
  props: {
    name: {
      type: String,
      required: false // <-- subcategories might not have section name
    },
    order: {
      type: Number,
      required: true
    },
    categories: {
      type: Array
    },
    isCollapse: {
      type: Boolean,
      default: false
    }
  },
  data() {
    return {
      isDropdown: false
    }
  },
  methods: {
    collapse() {
      store.dispatch('forums/collapse', this.categories[0]);
      this.$emit('collapse', this.categories[0].id);
    },

    hideDropdown() {
      this.isDropdown = false;
    },

    isBeginning(index) {
      let result = true;

      while (index > 0) {
        if (!this.categories[--index].is_hidden) {
          result = false;
        }
      }

      return result;
    },

    isEnding(index) {
      let result = true;
      const length = this.categories.length - 1;

      while (index < length) {
        if (!this.categories[++index].is_hidden) {
          result = false;
        }
      }

      return result;
    },

    className(name) {
      return 'logo-' + name.toLowerCase().replace(/[^a-z]/g, "");
    },

    getUrl(category) {
      return category.topic.is_read ? `${category.topic.url}?p=${category.post.id}#id${category.post.id}` : category.topic.url;
    },

    ...mapActions('forums', ['mark', 'toggle', 'up', 'down'])
  },
  computed: mapGetters('user', ['isAuthorized'])
}
</script>
