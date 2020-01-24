<template>
  <div class="section margin-md-top">
    <div class="section-name">
      <h2 class="pull-left">
        <a href="javascript:" @click="collapse">
          <i :class="[isCollapse ? 'fa-plus-square': 'fa-minus-square']" class="far"></i>  {{ name }}
        </a>
      </h2>

      <div v-if="isAuthorized" :class="{'open': isDropdown}" v-on-clickaway="hideDropdown" class="dropdown pull-right">
        <a href="javascript:" @click="isDropdown = ! isDropdown" class="panel-cog"><i class="fas fa-cogs"></i></a>

        <ul class="dropdown-menu">
          <li v-for="category in categories">
            <a href="javascript:" @click="toggleCategory(category)">
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
            <i :class="{'new': !category.is_read}" class="logo far fa-comments pull-left visible-lg"></i>

            <div class="wrap">
              <h3><a :href="category.url">{{ category.name }}</a></h3>

              <p class="description hidden-sm hidden-xs hidden-md">
                {{ category.description }}
              </p>

              <ul v-if="category.children" class="list-unstyled list-inline list-sub hidden-sm hidden-xs hidden-md">
                <li v-for="children in category.children">
                  <i :class="{'new': !category.is_read}" class="far fa-file"></i>

                  <a :href="children.url">{{ children.name }}</a>
                </li>
              </ul>
            </div>
          </div>

          <div class="col-lg-1 col-xs-12 col-sm-6 col-lg-stat">
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

          <div class="col-lg-4 col-xs-12 col-sm-12">
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
                  <a href="javascript:" title="Oznacz jako przeczytane" @click="asRead(category)"><i class="far fa-eye"></i></a>

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
  import { mapGetters } from "vuex";

  Vue.use(VueTimeago);

  export default {
    mixins: [ mixins, clickaway ],
    store,
    props: {
      name: {
        type: String,
        required: true
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
      toggleCategory(category) {
        category.is_hidden = ! category.is_hidden;

        this.$emit('toggle', category);
      },

      collapse() {
        this.isCollapse = ! this.isCollapse;
      },

      hideDropdown() {
        this.isDropdown = false;
      },

      asRead(category) {
        category.is_read = true;

        this.$emit('mark', category);
      },

      up(category) {
        let aboveIndex = this._findIndex(category.order - 1, category.section);

        if (aboveIndex > -1) {
          category.order -= 1;
          this.categories[aboveIndex].order += 1;

          this.$emit('order', [category, this.categories[aboveIndex]]);
        }
      },

      down(category) {
        let beyondIndex = this._findIndex(category.order + 1, category.section);

        if (beyondIndex > -1) {
          category.order += 1;
          this.categories[beyondIndex].order -= 1;

          this.$emit('order', [category, this.categories[beyondIndex]]);
        }
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

      _findIndex(order, section) {
        return this.categories.findIndex(value => value.order === order && value.section === section);
      }
    },
    computed: mapGetters('user', ['isAuthorized'])
  }
</script>
