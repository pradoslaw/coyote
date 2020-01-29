<template>
  <div class="panel-section panel">
    <div class="section-name">
      <h2 class="pull-left">
        <a href="javascript:" @click="collapse">
          <i :class="[isCollapse ? 'fa-plus-square': 'fa-minus-square']" class="far"></i>  {{ name }}
        </a>
      </h2>

      <div v-if="isAuthorized && !categories[0].parent_id" :class="{'open': isDropdown}" v-on-clickaway="hideDropdown" class="dropdown pull-right">
        <a href="javascript:" @click="isDropdown = ! isDropdown" class="panel-cog"><i class="fas fa-cogs"></i></a>

        <ul class="dropdown-menu">
          <li v-for="category in categories">
            <a href="javascript:" @click="toggle(category)">
              <i :class="{'fa-check': !category.is_hidden}" class="fa"></i>

              {{ category.name }}
            </a>
          </li>
        </ul>
      </div>

      <div class="clearfix"></div>
    </div>

    <section v-if="!isCollapse" class="panel panel-default panel-categories">
      <div v-for="(category, index) in categories" v-if="!category.is_hidden" :class="{'new': !category.is_read}" class="panel-body">
        <div class="row">
          <div class="col-lg-7 col-xs-12 col-sm-6 col-main">
            <i v-if="category.is_locked" class="logo fas fa-lock pull-left visible-lg"></i>
            <a v-else :href="category.url">
              <i :class="className(category.name)" class="logo far fa-comments pull-left visible-lg">
                <b v-if="!category.is_read"></b>
              </i>
            </a>

            <div class="wrap">
              <h3><a :href="category.url">{{ category.name }}</a></h3>

              <p class="description hidden-sm hidden-xs hidden-md">
                {{ category.description }}
              </p>

              <ul v-if="category.children" class="list-unstyled list-inline list-sub hidden-sm hidden-xs hidden-md">
                <li v-for="children in category.children">
                  <i :class="{'new': !children.is_read}" class="far fa-file"></i>

                  <a :href="children.url">{{ children.name }}</a>
                </li>
              </ul>
            </div>
          </div>

          <div v-if="category.is_redirected" class="col-lg-5 col-xs-12 col-sm-6 text-center">
            Liczba przekierowań: {{ category.redirects }}
          </div>

          <div v-if="!category.is_redirected" class="col-lg-1 col-xs-12 col-sm-6 col-lg-stat">
            <div class="row">
              <div class="col-lg-12 col-xs-6 counter">
                <strong>{{ category.topics | number }}</strong>
                <small class="text-muted text-wide-spacing">wątki</small>
              </div>

              <div class="col-lg-12 col-xs-6 counter">
                <strong>{{ category.posts | number }}</strong>
                <small class="text-muted text-wide-spacing">postów</small>
              </div>
            </div>
          </div>

          <div v-if="!category.is_redirected" class="col-lg-4 col-xs-12 col-sm-12">
            <div v-if="category.post" class="media">
              <div class="media-left hidden-xs">
                <a v-profile="category.post.user.id">
                  <object :data="category.post.user.photo || '//'" type="image/png" class="media-object img-thumbnail">
                    <img src="/img/avatar.png" :alt="category.post.user.name">
                  </object>
                </a>
              </div>
              <div class="media-body">
                <p class="subject">
                  <a :href="category.topic.url + '?view=unread'">{{ category.topic.subject }}</a>
                </p>

                <small class="text-muted"><vue-timeago :datetime="category.post.created_at"></vue-timeago></small>, <a v-profile="category.post.user.id">{{ category.post.user.name }}</a>

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
  import { default as mixins } from '../mixins/user';
  import VueTimeago from '../../plugins/timeago';
  import { mixin as clickaway } from 'vue-clickaway';
  import store from '../../store';
  import { mapGetters, mapActions } from "vuex";

  Vue.use(VueTimeago);

  export default {
    mixins: [ mixins, clickaway ],
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
      }
    },
    data() {
      return {
        isCollapse: false,
        isDropdown: false
      }
    },
    methods: {
      collapse() {
        this.isCollapse = ! this.isCollapse;
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
        return 'logo-' + name.toLowerCase().replace(/[^\x00-\x7F]/g, "");
      },

      ...mapActions('forums', ['mark', 'toggle', 'up', 'down'])
    },
    computed: mapGetters('user', ['isAuthorized'])
  }
</script>
