<template>
  <div :class="{'unread': ! notification.is_read}" class="notification">
    <div :title="notification.headline" class="media">
      <a class="user-link me-2" :href="`/Profile/${notification.user_id}`" title="Kliknij, aby wyświetlić profil użytkownika">
        <div class="neon-avatar-border">
          <vue-avatar
            :photo="notification.photo"
            :initials="notification.initials"
            class="i-35"/>
        </div>
      </a>
      <a @mousedown="markAsRead(notification)" @touchstart="markAsRead(notification)" :href="notification.url" class="media-body text-truncate">
        <header class="notification-header">
          <h4 class="text-truncate">{{ notification.headline }}</h4>
          <small>
            <vue-timeago :datetime="notification.created_at"></vue-timeago>
          </small>
        </header>
        <h3 class="notification-subject text-truncate">{{ notification.subject }}</h3>
        <p class="notification-content text-truncate">{{ notification.excerpt }}</p>
      </a>
    </div>
    <a @click.stop="deleteNotification(notification)" href="javascript:" class="btn-action" title="Usuń">
      <vue-icon name="notificationDelete"/>
    </a>
  </div>
</template>

<script>
import {VueTimeAgo} from '../../plugins/timeago';
import store from '../../store';
import VueAvatar from '../avatar.vue';
import VueIcon from '../icon';

export default {
  components: {
    VueIcon,
    VueAvatar,
    'vue-timeago': VueTimeAgo,
  },
  props: {
    notification: {
      type: Object,
    },
  },
  store,
  methods: {
    markAsRead(notification) {
      notification.url = `/notification/${notification.id}`;
      store.commit('notifications/mark', notification);
    },
    deleteNotification(notification) {
      store.dispatch('notifications/remove', notification);
    },
  },
};
</script>
