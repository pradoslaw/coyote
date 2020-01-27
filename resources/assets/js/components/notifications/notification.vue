<template>
  <div :class="{'unread': ! notification.is_read}" class="notification">
    <a @mousedown="markAsRead(notification)" :href="notification.url" :title="notification.headline" class="notification-link">
      <div class="media">
        <div class="media-left">
          <object class="media-object" :data="notification.photo || '//'" type="image/png">
            <img src="/img/avatar.png">
          </object>
        </div>

        <div class="media-body">
          <header>
            <h4>{{ notification.headline }}</h4>
            <small>{{ notification.created_at }}</small>
          </header>

          <h3>{{ notification.subject }}</h3>
          <p>{{ notification.excerpt }}</p>
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

  export default {
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
