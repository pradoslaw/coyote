<template>
  <div :class="{'unread': ! notification.is_read}" class="notification">
    <a @mousedown="markAsRead(notification)" @touchstart="markAsRead(notification)" :href="notification.url" :title="notification.headline" class="notification-link">
      <div class="media">
        <vue-avatar :photo="notification.photo" class="media-object mr-2"></vue-avatar>

        <div class="media-body text-truncate">
          <header>
            <h4 class="text-truncate">{{ notification.headline }}</h4>
            <small><vue-timeago :datetime="notification.created_at"></vue-timeago></small>
          </header>

          <h3 class="text-truncate">{{ notification.subject }}</h3>
          <p class="text-truncate">{{ notification.excerpt }}</p>
        </div>
      </div>
    </a>

    <a @click.stop="deleteNotification(notification)" href="javascript:" class="btn-delete-alert" title="Usuń">
      <i class="fas fa-times"></i>
    </a>
  </div>
</template>

<script>
  import store from '../../store';
  import VueTimeago from '../../plugins/timeago';
  import VueAvatar from '../avatar.vue';

  Vue.use(VueTimeago);

  export default {
    components: { 'vue-avatar': VueAvatar },
    props: {
      notification: {
        type: Object
      }
    },
    store,
    methods: {
      markAsRead(notification) {
        notification.url = `/notification/${notification.id}`;

        this.$store.commit('notifications/mark', notification);
      },

      deleteNotification(notification) {
        this.$store.dispatch('notifications/remove', notification);
      }
    }
  }
</script>
