<template>
  <div :title="message.excerpt" :class="{'unread': ! isRead}" class="notification">
    <a :href="message.url" class="notification-link">
      <div class="media">
        <object class="media-object mr-2" :data="message.user.photo || '//'" type="image/png">
          <img src="/img/avatar.png">
        </object>

        <div class="media-body">
          <header class="text-truncate">
            <h4>{{ message.user.name }}</h4>
            <small><vue-timeago :datetime="message.created_at"></vue-timeago></small>
          </header>

          <p class="text-truncate">
            <template v-if="message.folder === SENTBOX">
              <i v-if="message.read_at" class="fas fa-check"></i>
              <span v-else>Ty: </span>
            </template>

            {{ message.excerpt }}
          </p>
        </div>
      </div>
    </a>
  </div>
</template>

<script>
  import VueTimeago from '../../plugins/timeago';

  Vue.use(VueTimeago);

  export default {
    props: {
      message: {
        type: Object
      }
    },
    data() {
      return {
        SENTBOX: 2
      }
    },
    computed: {
      isRead() {
        return this.message.folder !== this.SENTBOX ? (this.message.read_at !== null) : true;
      }
    }
  }
</script>
