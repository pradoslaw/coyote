<template>
  <div :class="{'unread': ! notification.is_read}" class="notification">
    <div :title="notification.headline" class="media">
      <a :href="`/Profile/${notification.user_id}`" title="Kliknij, aby wyświetlić profil użytkownika">
        <vue-avatar :photo="notification.photo" class="i-35 mr-2"></vue-avatar>
      </a>

      <a @mousedown="markAsRead(notification)" @touchstart="markAsRead(notification)" :href="notification.url" class="media-body text-truncate">
        <header class="notification-header">
          <h4 class="text-truncate">{{ notification.headline }}</h4>
          <small><vue-timeago :datetime="notification.created_at"></vue-timeago></small>
        </header>

        <h3 class="notification-subject text-truncate">{{ notification.subject }}</h3>
        <p class="notification-content text-truncate">{{ notification.excerpt }}</p>
      </a>
    </div>

    <a @click.stop="deleteNotification(notification)" href="javascript:" class="btn-action" title="Usuń">
      <i class="fas fa-times"></i>
    </a>
  </div>
</template>

<script>
  import Vue from 'vue';
  import store from '@/store';
  import VueTimeago from '@/plugins/timeago';
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
