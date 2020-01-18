<template>
  <div class="section">
    <h2 class="section-name">
      <a href="#"><i class="far fa-minus-square"></i>  {{ name }}</a>

      <a href="#" class="panel-cog pull-right"><i class="fas fa-cogs"></i></a>
    </h2>

    <section v-for="category in categories" class="panel panel-default panel-categories">
      <div class="panel-body new">
        <div class="row">
          <div class="col-lg-7 col-xs-12 col-sm-6 col-main">
            <i :class="{'new': !category.is_read}" class="far fa-comments fa-2x pull-left visible-lg"></i>

            <div class="wrap">
              <h3><a :href="category.url">{{ category.name }}</a></h3>

              <p class="description hidden-sm hidden-xs hidden-md">
                {{ category.description }}
              </p>
            </div>
          </div>

          <div class="col-lg-1 col-xs-12 col-sm-6 col-lg-stat">
            <div class="row">
              <div class="col-lg-12 col-xs-6 counter">
                <strong>{{ category.topics }}</strong>
                <small class="text-muted text-wide-spacing">wątki</small>
              </div>

              <div class="col-lg-12 col-xs-6 counter">
                <strong>{{ category.posts }}</strong>
                <small class="text-muted text-wide-spacing">postów</small>
              </div>
            </div>
          </div>

          <div class="col-lg-4 col-xs-12 col-sm-12">
            <div class="media">
              <div class="media-left hidden-xs">
                <a v-profile="category.post.user.id">
                  <object :data="category.post.user.photo || '//'" type="image/png" class="media-object img-thumbnail">
                    <img src="/img/avatar.png" :alt="category.post.user.name">
                  </object>
                </a>
              </div>
              <div class="media-body">
                <p class="subject">
                  <a :href="category.post.url">{{ category.topic.subject }}</a>
                </p>

                <small class="text-muted"><vue-timeago :datetime="category.post.created_at"></vue-timeago></small>, <a v-profile="category.post.user.id">{{ category.post.user.name }}</a>

                <div class="toolbox">
                  <a href="#"><i class="far fa-eye"></i></a>

                  <a href="#"><i class="fas fa-caret-up"></i></a>
                  <a href="#"><i class="fas fa-caret-down"></i></a>
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

  Vue.use(VueTimeago);

  export default {
    mixins: [ mixins ],
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
    }
  }
</script>
